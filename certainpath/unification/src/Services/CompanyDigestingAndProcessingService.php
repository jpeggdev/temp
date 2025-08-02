<?php

namespace App\Services;

use App\Entity\Company;
use App\Exceptions\NotFoundException\CompanyNotFoundException;
use App\Message\CompanyProcessingMessage;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Throwable;

class CompanyDigestingAndProcessingService
{
    private const PROCESS_RECORD_LIMIT = 10000;

    public function __construct(
        private readonly DataStreamDigestingService $dataStreamDigestingService,
        private readonly PostProcessingService $postProcessingService,
        private readonly CustomerMetricsService $customerMetricsService,
        private readonly CompanyRepository $companyRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly CompanyJobService $jobService,
        private readonly TenantStreamAuditService $streamAudit,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function dispatchCompanyProcessingByIdentifier(
        string $companyIdentifier
    ): void {
        $company = $this->companyRepository->findActiveByIdentifierOrCreate(
            $companyIdentifier
        );
        $this->logger->info(
            'Dispatching company processing message',
            ['company' => $companyIdentifier]
        );
        $prospectCount = $this->streamAudit->getProspectsCount(
            $companyIdentifier
        );
        $memberCount = $this->streamAudit->getMembersCount(
            $companyIdentifier
        );
        $invoiceCount = $this->streamAudit->getInvoiceCount(
            $companyIdentifier
        );
        $doCustomerProcessing =
            $prospectCount <= self::PROCESS_RECORD_LIMIT
            && $memberCount <= self::PROCESS_RECORD_LIMIT
            && $invoiceCount <= self::PROCESS_RECORD_LIMIT;
        $companyProcessingMessage = new CompanyProcessingMessage(
            $companyIdentifier
        );
        $companyProcessingMessage->doCustomerProcessing = $doCustomerProcessing;
        $this->messageBus->dispatch(
            $companyProcessingMessage
        );
    }

    /**
     * @throws CompanyNotFoundException
     * @throws Throwable
     */
    public function handleCompanyProcessingMessage(
        CompanyProcessingMessage $message
    ): void {
        $timeStamp = time();
        if (!$message->jobIdentifier) {
            $message->jobIdentifier =
                'DIGEST-' . $timeStamp;
        }
        echo
            $message->companyIdentifier
            .
            ' Handling company processing message'
        . PHP_EOL;
        $this->logger->info(
            'Handling company processing message',
            [
                'company' => $message->companyIdentifier,
                'job' => $message->jobIdentifier
            ]
        );
        $company = $this->companyRepository->findOneByIdentifier(
            $message->companyIdentifier
        );
        if (!$company) {
            $this->failJobForCompanyWithMessage(
                $message,
                $company
            );
            throw new CompanyNotFoundException(
                $message->companyIdentifier
            );
        }
        $activeJobsForCompany = $this->jobService->getCompanyActiveJobEventCount(
            $company
        );
        if (2 <= $activeJobsForCompany) {
            $this->logger->info(
                'Company has 2 or more active jobs, skipping',
                ['company' => $message->companyIdentifier]
            );
            return;
        }
        if (1 === $activeJobsForCompany) {
            $this->logger->info(
                '1 Job in progress, delaying this one',
                [
                    'company' => $message->companyIdentifier,
                    'job' => $message->jobIdentifier
                ]
            );
            $this->messageBus->dispatch(
                $message,
                [new DelayStamp(60000)] // 1 minute
            );
            return;
        }

        $this->startJobForCompany($message);
        $this->processCompanyByIdentifier(
            $message->companyIdentifier,
            $message->doCustomerProcessing,
            $message
        );
        $company = $this->companyRepository->findOneByIdentifier(
            $message->companyIdentifier
        );
        $this->jobService->completeJobForCompany(
            $company,
            $message->jobIdentifier
        );
        // audit stream tables for this tenant

        $countOfStreamRecordsOutstanding =
            $this->streamAudit->countOutstandingRecordsForTenant(
                $company->getIdentifier()
            );

        if ($countOfStreamRecordsOutstanding > 0) {
            $this->logger->info(
                'Stream records outstanding for company',
                [
                    'company' => $company->getIdentifier(),
                    'count' => $countOfStreamRecordsOutstanding
                ]
            );
            $this->dispatchCompanyProcessingByIdentifier(
                $company->getIdentifier()
            );
        }
    }

    /**
     * @throws CompanyNotFoundException
     * @throws Exception
     * @throws Throwable
     */
    public function processCompanyByIdentifier(
        string $companyIdentifier,
        bool $doCustomerMetrics = true,
        CompanyProcessingMessage $message = null
    ): void {
        $company = $this->companyRepository->findOneByIdentifier(
            $companyIdentifier
        );
        if (!$company) {
            throw new CompanyNotFoundException(
                $companyIdentifier
            );
        }
        $this->processCompany(
            $company,
            $doCustomerMetrics,
            $message
        );
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    private function processCompany(
        Company $company,
        bool $doCustomerMetrics = true,
        CompanyProcessingMessage $message = null
    ): void {
        $this->logger->info('Digesting company', ['company' => $company->getIdentifier()]);
        try {
            $this->dataStreamDigestingService
                ->setLimit(self::PROCESS_RECORD_LIMIT)
                ->setDeleteRemote(true)
                ->syncSources($company);
            $this->flushAndClearForCompanyAndMessage(
                $company,
                $message
            );
        } catch (Throwable $e) {
            $this->failJobForCompanyWithMessage($message, $company);
            $this->logger->warning(
                'Error digesting company, Moving on though.',
                [
                    'company' => $company->getIdentifier(),
                    'error' => $e->getMessage()
                ]
            );
        }
        $this->logger->info('Post Processing company', ['company' => $company->getIdentifier()]);
        try {
            $this->postProcessingService
                ->setRecordLimit(self::PROCESS_RECORD_LIMIT)
                ->processRecords($company->getIdentifier());
            $this->flushAndClearForCompanyAndMessage(
                $company,
                $message
            );
        } catch (Throwable $e) {
            $this->failJobForCompanyWithMessage($message, $company);
            $this->logger->warning(
                'Error post processing company, Moving on though.',
                [
                    'company' => $company->getIdentifier(),
                    'error' => $e->getMessage()
                ]
            );
        }
        if ($doCustomerMetrics) {
            $this->logger->info('Updating customer metrics', ['company' => $company->getIdentifier()]);
            try {
                $this->customerMetricsService->updateCustomerMetricsForCompany(
                    $company
                );
                $this->flushAndClearForCompanyAndMessage(
                    $company,
                    $message
                );
            } catch (Throwable $e) {
                $this->failJobForCompanyWithMessage($message, $company);
                $this->logger->warning(
                    'Error updating customer metrics, Moving on though.',
                    [
                        'company' => $company->getIdentifier(),
                        'error' => $e->getMessage()
                    ]
                );
            }
        } else {
            $this->logger->info('Skipping customer metrics', ['company' => $company->getIdentifier()]);
        }
    }

    /**
     * @throws CompanyNotFoundException
     * @throws Throwable
     */
    private function startJobForCompany(
        CompanyProcessingMessage $message
    ): void {
        $company = $this->companyRepository->findOneByIdentifier(
            $message->companyIdentifier
        );
        if (!$company) {
            throw new CompanyNotFoundException(
                $message->companyIdentifier
            );
        }
        if (
            !$this->jobService->isJobInProgressForCompany(
                $company,
                $message->jobIdentifier
            )
        ) {
            $this->jobService->startJobForCompany(
                $company,
                $message->jobIdentifier
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function failJobForCompanyWithMessage(
        ?CompanyProcessingMessage $message,
        Company $company
    ): void {
        if ($message) {
            $this->logger->info('Failing job for company', ['company' => $company->getIdentifier()]);
            try {
                $this->jobService->failJobForCompany(
                    $company,
                    $message->jobIdentifier
                );
            } catch (Exception $e) {
                $this->logger->warning(
                    'Error failing job for company',
                    [
                        'company' => $company->getIdentifier(),
                        'error' => $e->getMessage()
                    ]
                );
            }
        }
    }

    private function flushAndClearForCompanyAndMessage(
        Company $company,
        ?CompanyProcessingMessage $message
    ): void {
        try {
            if ($this->entityManager->isOpen()) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            } else {
                $this->logger->warning(
                    'flushAndClearForCompanyAndMessage: Entity Manager is closed',
                    [
                        'company' => $company->getIdentifier(),
                        'job' => $message?->jobIdentifier
                    ]
                );
            }
        } catch (Throwable $e) {
            $this->logger->warning(
                'Error flushing and clearing for company',
                [
                    'company' => $company->getIdentifier(),
                    'error' => $e->getMessage(),
                    'job' => $message?->jobIdentifier
                ]
            );
        }
    }
}

<?php

namespace App\Services;

use App\Entity\CompanyJobEvent;
use App\Entity\EventStatus;
use App\Entity\Company;
use App\Repository\CompanyJobEventRepository;
use App\Repository\EventStatusRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Throwable;

readonly class CompanyJobService
{
    public function __construct(
        private CompanyJobEventRepository $companyJobEventRepository,
        private EventStatusRepository $eventStatusRepository,
        private CompanyRepository $companyRepository,
        private ManagerRegistry $registry,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function startJobForCompany(
        Company $company,
        string $jobName
    ): void {
        $startEvent = new CompanyJobEvent();
        $startEvent->setCompany($company);
        $startEvent->setJobName($jobName);
        $startEvent->setEventStatus(
            $this
                ->eventStatusRepository
                ->findByEventCampaignStatus(
                    EventStatus::created()
                )
        );

        $this->addEvent($startEvent);
    }

    /**
     * @throws Throwable
     */
    public function failJobForCompany(
        Company $company,
        string $jobName
    ): void {
        $this->checkAndResetEntityManager();
        $company = $this->companyRepository->findOneByIdentifier(
            $company->getIdentifier()
        );
        $status = $this
            ->eventStatusRepository
            ->findByEventCampaignStatus(
                EventStatus::failed()
            );
        $endEvent = new CompanyJobEvent();
        $endEvent->setCompany($company);
        $endEvent->setJobName($jobName);
        $endEvent->setEventStatus(
            $status
        );

        $this->addEvent($endEvent);
    }

    public function isJobInProgressForCompany(
        Company $company,
        string $jobName
    ): bool {
        $jobStatus =
            $this
                ->companyJobEventRepository
                ->getLastEventForCompanyJobName(
                    $company,
                    $jobName
                );
        if (!$jobStatus) {
            return false;
        }
        return
            !$jobStatus->getEventStatus()->isFailed()
            &&
            !$jobStatus->getEventStatus()->isCompleted();
    }

    public function getCompanyActiveJobEventCount(
        Company $company
    ): int {
        $jobs = $this
            ->companyJobEventRepository
            ->getJobNamesForCompany(
                $company
            );
        $activeJobCount = 0;
        foreach ($jobs as $job) {
            if (
                $this->isJobInProgressForCompany(
                    $company,
                    $job['jobName']
                )
            ) {
                $activeJobCount++;
            }
        }
        return $activeJobCount;
    }

    /**
     * @throws Throwable
     */
    public function completeJobForCompany(
        Company $company,
        string $jobName
    ): void {
        $endEvent = new CompanyJobEvent();
        $endEvent->setCompany($company);
        $endEvent->setJobName($jobName);
        $endEvent->setEventStatus(
            $this
                ->eventStatusRepository
                ->findByEventCampaignStatus(
                    EventStatus::completed()
                )
        );

        $this->addEvent($endEvent);
    }

    public function isJobEndedForCompany(Company $company, string $string): bool
    {
        $jobStatus =
            $this
                ->companyJobEventRepository
                ->getLastEventForCompanyJobName(
                    $company,
                    $string
                );
        if (!$jobStatus) {
            return false;
        }
        return
            $jobStatus->getEventStatus()->isCompleted()
            ||
            $jobStatus->getEventStatus()->isFailed()
        ;
    }

    /**
     * @throws Throwable
     */
    private function addEvent(CompanyJobEvent $eventToAdd): void
    {
        try {
            $this->companyJobEventRepository->addEvent(
                $eventToAdd
            );
        } catch (Throwable $e) {
            if (!$eventToAdd->getCompany()) {
                $this->logger->warning(
                    'Company not found for event',
                    [
                        'event' => $eventToAdd->getJobName(),
                    ]
                );
                throw $e;
            }
            $this->checkAndResetEntityManager();
            $company = $this->companyRepository->findOneByIdentifier(
                $eventToAdd->getCompany()->getIdentifier()
            );
            $status = $this->eventStatusRepository->findByEventCampaignStatus(
                $eventToAdd->getEventStatus()
            );
            if ($company) {
                $eventToAdd->setCompany($company);
                $eventToAdd->setEventStatus($status);
                $this->companyJobEventRepository->addEvent(
                    $eventToAdd
                );
            }
        }
    }

    private function checkAndResetEntityManager(): void
    {
        if (!$this->entityManager->isOpen()) {
            $this->registry->resetManager();
        }
    }
}

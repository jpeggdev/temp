<?php

namespace App\MessageHandler;

use App\Entity\CompanyDataImportJob;
use App\Entity\FieldServiceSoftware;
use App\Entity\Trade;
use App\Message\CompanyFieldServiceImportCreated;
use App\Repository\CompanyDataImportJobRepository;
use App\Repository\FieldServiceSoftwareRepository;
use App\Repository\TradeRepository;
use App\Service\FieldServicesUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CompanyFieldServiceImportCreatedHandler
{
    public function __construct(
        private CompanyDataImportJobRepository $importRepository,
        private FieldServiceSoftwareRepository $softwareRepository,
        private TradeRepository $tradeRepository,
        private FieldServicesUploadService $fieldServicesUploadService,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(CompanyFieldServiceImportCreated $message): void
    {
        $importId = $message->getImportId();
        $import = $this->importRepository->find($importId);

        if (!$import instanceof CompanyDataImportJob) {
            $this->logger->warning("Import record not found for ID {$importId}");

            return;
        }

        $import->setStatus('PROCESSING');
        $this->em->flush();

        $trade = null;
        if ($import->getTrade()) {
            $trade = $this->tradeRepository->getTrade(
                Trade::fromLongName($import->getTrade())
            );
        }

        $software = null;
        if ($import->getSoftware()) {
            $software = $this->softwareRepository->getSoftware(
                FieldServiceSoftware::fromName($import->getSoftware())
            );
        }

        if (null === $trade) {
            $this->logger->warning("Unsupported trade: {$import->getTrade()}");
            $import->setProgressPercent('100');
            $import->setStatus('FAILED');
            $import->setErrorMessage('Unsupported trade');
            $this->em->flush();

            return;
        }
        if (null === $software) {
            $this->logger->warning("Unsupported software: {$import->getSoftware()}");
            $import->setProgressPercent('100');
            $import->setStatus('FAILED');
            $import->setErrorMessage('Unsupported software');
            $this->em->flush();

            return;
        }

        try {
            if ($import->isJobsOrInvoiceFile()) {
                $importedCount = $this->fieldServicesUploadService->processJobsOrInvoiceFile(
                    $import->getFilePath(),
                    $import->getCompany(),
                    $trade,
                    $software,
                    $import->getId()
                );
            } elseif ($import->isActiveClubMemberFile()) {
                $importedCount = $this->fieldServicesUploadService->processMembersFile(
                    $import->getFilePath(),
                    $import->getCompany(),
                    true,
                    $trade,
                    $software,
                    $import->getId()
                );
            } elseif ($import->isMemberFile()) {
                $importedCount = $this->fieldServicesUploadService->processMembersFile(
                    $import->getFilePath(),
                    $import->getCompany(),
                    false,
                    $trade,
                    $software,
                    $import->getId()
                );
            } else {
                $this->logger->warning('No valid file type in CompanyFieldServiceImport record');
                $import->setProgressPercent('100');
                $import->setStatus('FAILED');
                $import->setErrorMessage('No valid file type in CompanyFieldServiceImport record');
                $this->em->flush();

                return;
            }

            $import->setProgress("Upload Count: {$importedCount}");
            $this->em->flush();
        } catch (\Throwable $e) {
            $this->logger->error('Could not process the uploaded file', [
                'id' => $import->getId(),
                'error' => $e->getMessage(),
            ]);
            $import->setStatus('FAILED');
            $import->setProgressPercent('100');
            $import->setErrorMessage($e->getMessage());
            $this->em->flush();
        }
    }
}

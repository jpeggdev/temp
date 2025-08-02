<?php

namespace App\MessageHandler;

use App\Entity\CompanyDataImportJob;
use App\Message\CompanyProspectImportCreated;
use App\Repository\CompanyDataImportJobRepository;
use App\Service\ProspectSourceUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CompanyProspectImportCreatedHandler
{
    public function __construct(
        private CompanyDataImportJobRepository $importRepository,
        private ProspectSourceUploadService $prospectSourceUploadService,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
    ) {
    }

    public function __invoke(CompanyProspectImportCreated $message): void
    {
        $importId = $message->getImportId();
        $import = $this->importRepository->find($importId);

        if (!$import instanceof CompanyDataImportJob) {
            $this->logger->warning("Prospect import record not found for ID {$importId}");

            return;
        }

        $import->setStatus('PROCESSING');
        $this->em->flush();

        try {
            $this->prospectSourceUploadService->processProspectsFile($import);
            $import->setProgress('Uploaded prospect data successfully');
            $this->em->flush();
        } catch (\Throwable $e) {
            $this->logger->error('Could not process the uploaded prospect file', [
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

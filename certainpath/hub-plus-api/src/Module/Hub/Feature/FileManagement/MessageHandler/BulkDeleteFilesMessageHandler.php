<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\MessageHandler;

use App\Entity\FileDeleteJob;
use App\Entity\FilesystemNode;
use App\Module\Hub\Feature\FileManagement\Exception\BulkOperationException;
use App\Module\Hub\Feature\FileManagement\Exception\NonEmptyFolderException;
use App\Module\Hub\Feature\FileManagement\Message\BulkDeleteFilesMessage;
use App\Module\Hub\Feature\FileManagement\Service\BulkDeleteFilesystemNodesService;
use App\Repository\FileDeleteJobRepository;
use App\Repository\FilesystemNodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class BulkDeleteFilesMessageHandler
{
    private ManagerRegistry $doctrine;
    private MessageBusInterface $messageBus;
    private FileDeleteJobRepository $fileDeleteJobRepository;
    private FilesystemNodeRepository $filesystemNodeRepository;

    public function __construct(
        ManagerRegistry $doctrine,
        MessageBusInterface $messageBus,
        FileDeleteJobRepository $fileDeleteJobRepository,
        FilesystemNodeRepository $filesystemNodeRepository,
    ) {
        $this->doctrine = $doctrine;
        $this->messageBus = $messageBus;
        $this->fileDeleteJobRepository = $fileDeleteJobRepository;
        $this->filesystemNodeRepository = $filesystemNodeRepository;
    }

    public function __invoke(BulkDeleteFilesMessage $message): void
    {
        $em = $this->getEntityManager();

        $jobRepository = $this->fileDeleteJobRepository;
        $nodeRepository = $this->filesystemNodeRepository;

        $job = $jobRepository->findOneBy(['uuid' => $message->getJobUuid()]);

        if (!$job) {
            return;
        }

        $job->setStatus('processing');
        $em->flush();

        $fileUuids = $job->getFileUuids();
        $totalFiles = count($fileUuids);
        $processedFiles = 0;
        $successfulDeletes = 0;
        $failedItems = [];

        foreach ($fileUuids as $uuid) {
            if (!$em->isOpen()) {
                $this->doctrine->resetManager();
                $em = $this->getEntityManager();
                $jobRepository = $em->getRepository(FileDeleteJob::class);
                $nodeRepository = $em->getRepository(FilesystemNode::class);
            }

            $node = $nodeRepository->findOneBy(['uuid' => $uuid]);
            $nodeName = $node ? $node->getName() : 'Unknown file';
            $errorKey = $nodeName.' ('.$uuid.')';

            $deleteService = $this->getDeleteService($em);

            try {
                $result = $deleteService->deleteNodes([$uuid]);
                $successfulDeletes += $result['deleted'];
            } catch (NonEmptyFolderException $e) {
                $failedItems[$errorKey] = 'Cannot delete folder because it contains files or subfolders.';
            } catch (BulkOperationException $e) {
                if (method_exists($e, 'getFailedItems')) {
                    $exceptionFailedItems = $e->getFailedItems();
                    foreach ($exceptionFailedItems as $failedUuid => $failedReason) {
                        $failedItems[$errorKey] = $failedReason;
                    }
                } else {
                    $failedItems[$errorKey] = $e->getMessage();
                }
            }

            ++$processedFiles;

            if (!$em->isOpen()) {
                $this->doctrine->resetManager();
                $em = $this->getEntityManager();
                $jobRepository = $em->getRepository(FileDeleteJob::class);
            }

            try {
                $job = $jobRepository->findOneBy(['uuid' => $message->getJobUuid()]);
                if (!$job) {
                    return;
                }

                $percentComplete = ($processedFiles / $totalFiles) * 100;

                $job->setProcessedFiles($processedFiles);
                $job->setSuccessfulDeletes($successfulDeletes);
                $job->setFailedItems($failedItems);
                $job->setProgressPercent(number_format($percentComplete, 2));
                $em->flush();
            } catch (\Exception $e) {
                error_log('Failed to update job progress: '.$e->getMessage());
                // Reset the EntityManager and continue
                $this->doctrine->resetManager();
                $em = $this->getEntityManager();
                $jobRepository = $em->getRepository(FileDeleteJob::class);
            }
        }

        if (!$em->isOpen()) {
            $this->doctrine->resetManager();
            $em = $this->getEntityManager();
            $jobRepository = $em->getRepository(FileDeleteJob::class);
        }

        try {
            $job = $jobRepository->findOneBy(['uuid' => $message->getJobUuid()]);
            if ($job) {
                $job->setStatus('completed');
                $job->setProcessedFiles($totalFiles);
                $job->setSuccessfulDeletes($successfulDeletes);
                $job->setFailedItems($failedItems);
                $job->setProgressPercent('100.00');
                $em->flush();
            }
        } catch (\Exception $e) {
            error_log('Failed to finalize job: '.$e->getMessage());
        }
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $em = $this->doctrine->getManager();
        if (!$em instanceof EntityManagerInterface) {
            throw new \RuntimeException('Expected EntityManagerInterface, got '.get_class($em));
        }

        return $em;
    }

    /**
     * Create a new instance of the delete service with a fresh EntityManager.
     */
    private function getDeleteService(EntityManagerInterface $em): BulkDeleteFilesystemNodesService
    {
        /** @var FilesystemNodeRepository $filesystemNodeRepository */
        $filesystemNodeRepository = $em->getRepository(FilesystemNode::class);

        return new BulkDeleteFilesystemNodesService(
            $em,
            $filesystemNodeRepository,
            $this->messageBus
        );
    }
}

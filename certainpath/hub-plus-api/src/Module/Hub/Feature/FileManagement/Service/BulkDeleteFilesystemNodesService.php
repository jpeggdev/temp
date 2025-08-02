<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\Service;

use App\Entity\FileDeleteJob;
use App\Entity\Folder;
use App\Module\Hub\Feature\FileManagement\DTO\Response\BulkDeleteNodesQueueResponseDTO;
use App\Module\Hub\Feature\FileManagement\Exception\BulkOperationException;
use App\Module\Hub\Feature\FileManagement\Exception\NonEmptyFolderException;
use App\Module\Hub\Feature\FileManagement\Message\BulkDeleteFilesMessage;
use App\Repository\FilesystemNodeRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class BulkDeleteFilesystemNodesService
{
    public function __construct(
        private EntityManagerInterface $em,
        private FilesystemNodeRepository $filesystemNodeRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Queue nodes for deletion via background processing.
     *
     * @param array $uuids The UUIDs of the nodes to delete
     */
    public function queueNodesForDeletion(array $uuids): BulkDeleteNodesQueueResponseDTO
    {
        $deleteJob = new FileDeleteJob();
        $deleteJob->setStatus('pending');
        $deleteJob->setFileUuids($uuids);
        $deleteJob->setTotalFiles(count($uuids));
        $deleteJob->setProcessedFiles(0);
        $deleteJob->setSuccessfulDeletes(0);
        $deleteJob->setProgressPercent('0.00');
        $deleteJob->setFailedItems([]);

        $this->em->persist($deleteJob);
        $this->em->flush();

        $this->messageBus->dispatch(new BulkDeleteFilesMessage($deleteJob->getUuid()));

        return new BulkDeleteNodesQueueResponseDTO(
            jobId: $deleteJob->getUuid(),
            status: 'pending',
            totalFiles: count($uuids),
        );
    }

    /**
     * Delete multiple filesystem nodes immediately (synchronous).
     *
     * @param array $uuids The UUIDs of the nodes to delete
     *
     * @return array Results of the operation with count of deleted items
     *
     * @throws NonEmptyFolderException When a folder is not empty
     * @throws BulkOperationException  When some nodes could not be deleted
     */
    public function deleteNodes(array $uuids): array
    {
        $nodesToDelete = [];
        $failedItems = [];

        foreach ($uuids as $uuid) {
            $node = $this->filesystemNodeRepository->findOneBy(['uuid' => $uuid]);

            if (!$node) {
                $failedItems[$uuid] = 'Node not found';
                continue;
            }

            if ($node instanceof Folder && !$node->getChildren()->isEmpty()) {
                throw new NonEmptyFolderException();
            }

            $nodesToDelete[] = $node;
        }

        if (!empty($failedItems)) {
            $exception = new BulkOperationException('Some nodes could not be deleted');
            $exception->setFailedItems($failedItems);
            throw $exception;
        }

        $deletedCount = 0;

        foreach ($nodesToDelete as $node) {
            try {
                $this->em->remove($node);
                $this->em->flush();
                ++$deletedCount;
            } catch (\Exception $e) {
                if ($e instanceof ForeignKeyConstraintViolationException) {
                    $failedItems[$node->getUuid()] = 'Cannot delete file because it is being used by other items in the system';
                } else {
                    $failedItems[$node->getUuid()] = $e->getMessage();
                }
            }
        }

        if (!empty($failedItems)) {
            $exception = new BulkOperationException('Some nodes could not be deleted');
            $exception->setFailedItems($failedItems);
            throw $exception;
        }

        return [
            'deleted' => $deletedCount,
            'total' => count($uuids),
        ];
    }
}

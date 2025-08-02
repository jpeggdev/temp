<?php

namespace App\Services;

use App\Entity\Batch;
use App\Entity\BatchStatus;
use App\Entity\BulkBatchStatusEvent;
use App\Exceptions\DomainException\Batch\BulkUpdateBatchesStatusesCannotBeCompletedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Repository\BatchRepository;
use App\Repository\BatchStatusRepository;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;

readonly class BulkUpdateBatchesStatusService
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private BatchRepository $batchRepository,
        private BatchStatusRepository $batchStatusRepository,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     * @throws BulkUpdateBatchesStatusesCannotBeCompletedException
     */
    public function bulkUpdateStatus(int $year, int $week, string $status): array
    {
        $weekStartDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $weekEndDate = $weekStartDate->copy()->endOfWeek();
        $batchStatusNew = $this->batchStatusRepository->findOneByNameOrFail($status);

        if ($batchStatusNew->getName() === BatchStatus::STATUS_NEW) {
            return [];
        }

        $updatedBatches = [];

        $this->entityManager->beginTransaction();

        try {
            $batchesToUpdate = $this->batchRepository->findAllByWeekStartAndEndDate($weekStartDate, $weekEndDate);

            foreach ($batchesToUpdate as $batch) {
                if (!$this->shouldUpdateBatch($batch, $batchStatusNew)) {
                    continue;
                }

                $batchStatusCurrent = $batch->getBatchStatus()?->getName();
                $batch->setBatchStatus($batchStatusNew);
                $this->entityManager->persist($batch);

                $updatedBatches[] = [
                    'batch_id' => $batch->getId(),
                    'previous_value' => $batchStatusCurrent,
                    'new_value' => $status,
                ];
            }

            if (!empty($updatedBatches)) {
                $batchStatusBulkEvent = (new BulkBatchStatusEvent())
                    ->setYear($year)
                    ->setWeek($week)
                    ->setBatchStatus($batchStatusNew)
                    ->setUpdatedBatches($updatedBatches);

                $this->entityManager->persist($batchStatusBulkEvent);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->entityManager->rollback();
            throw new BulkUpdateBatchesStatusesCannotBeCompletedException();
        }

        return $updatedBatches;
    }

    private function shouldUpdateBatch(Batch $batch, BatchStatus $newStatus): bool
    {
        $immutableStatuses = [
            BatchStatus::STATUS_PAUSED,
            BatchStatus::STATUS_ARCHIVED,
            BatchStatus::STATUS_COMPLETE,
        ];

        $currentStatus = $batch->getBatchStatus()?->getName();
        $isStatusChangeAllowed = !in_array($currentStatus, $immutableStatuses, true);
        $isNewStatusDifferent = $currentStatus !== $newStatus->getName();

        return $isStatusChangeAllowed && $isNewStatusDifferent;
    }
}

<?php

namespace App\Services\DetailsMetadata;

use App\Entity\BatchStatus;
use App\Repository\BulkBatchStatusEventRepository;

readonly class GetBulkUpdateBatchStatusDetailsMetadataService
{
    private const STATUS_SEQUENCE = [
        BatchStatus::STATUS_NEW,
        BatchStatus::STATUS_SENT,
        BatchStatus::STATUS_PROCESSED,
        BatchStatus::STATUS_INVOICED,
        BatchStatus::STATUS_COMPLETE
    ];

    public function __construct(
        private BulkBatchStatusEventRepository $batchStatusBulkEventRepository,
    ) {
    }

    public function getDetailMetadata(int $year, int $week): array
    {
        $currentStatus = $this->getCurrentStatus($year, $week);
        return [
            'currentStatus' => $currentStatus,
            'bulkBatchStatusOptions' => $this->generateOptions($currentStatus),
        ];
    }

    private function generateOptions(string $currentStatus): array
    {
        $currentStatusIndex = array_search($currentStatus, self::STATUS_SEQUENCE, true);

        return array_map(fn($batchStatus, $index) => [
            'id' => $batchStatus,
            'label' => ucfirst($batchStatus),
            'description' => $this->getDescription($batchStatus),
            'enabled' => $batchStatus !== BatchStatus::STATUS_NEW && $index > $currentStatusIndex,
        ], self::STATUS_SEQUENCE, array_keys(self::STATUS_SEQUENCE));
    }

    private function getDescription(string $status): string
    {
        return [
            'new' => 'Batch created but not yet sent to CLI',
            'sent' => 'Batch has been sent to CLI for processing',
            'processed' => 'CLI has completed processing the batch',
            'invoiced' => 'Batch has been invoiced to the client',
            'complete' => 'Batch is fully complete with all processes finished',
        ][$status] ?? '';
    }

    private function getCurrentStatus(int $year, int $week): string
    {
        $batchStatusBulkEventStatus = $this->batchStatusBulkEventRepository->findAllByYearAndWeek(
            $year,
            $week
        );

        return $batchStatusBulkEventStatus->isEmpty()
            ? BatchStatus::STATUS_NEW
            : $batchStatusBulkEventStatus->first()->getBatchStatus()->getName();
    }
}

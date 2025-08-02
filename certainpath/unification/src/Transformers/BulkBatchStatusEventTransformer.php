<?php

namespace App\Transformers;

use App\Entity\BulkBatchStatusEvent;
use League\Fractal\TransformerAbstract;

class BulkBatchStatusEventTransformer extends TransformerAbstract
{
    public function transform(BulkBatchStatusEvent $batchStatusBatchEvent): array
    {
        return [
            'id' => $batchStatusBatchEvent->getId(),
            'year' => $batchStatusBatchEvent->getYear(),
            'week' => $batchStatusBatchEvent->getWeek(),
            'batchStatus' => $this->includeBatchStatus($batchStatusBatchEvent),
        ];
    }

    public function includeBatchStatus(BulkBatchStatusEvent $batchStatusBulkEvent): array
    {
        $batchStatus = $batchStatusBulkEvent->getBatchStatus();

        return $batchStatus
            ? (new BatchStatusTransformer())->transform($batchStatus)
            : [];
    }
}

<?php

namespace App\Transformers;

use App\Entity\BatchStatus;
use League\Fractal\TransformerAbstract;

class BatchStatusTransformer extends TransformerAbstract
{
    public function transform(BatchStatus $batchStatus): array
    {
        return [
            'id' => $batchStatus->getId(),
            'name' => $batchStatus->getName(),
        ];
    }
}

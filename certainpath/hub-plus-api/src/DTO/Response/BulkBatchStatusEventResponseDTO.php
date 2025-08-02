<?php

declare(strict_types=1);

namespace App\DTO\Response;

class BulkBatchStatusEventResponseDTO
{
    public function __construct(
        public int $id,
        public int $year,
        public int $week,
        public BatchStatusResponseDTO $batchStatus,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            year: $data['year'],
            week: $data['week'],
            batchStatus: BatchStatusResponseDTO::fromArray($data['batchStatus'])
        );
    }
}

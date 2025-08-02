<?php

namespace App\DTO\Response;

class GetBulkBatchStatusDetailsMetadataResponse
{
    public function __construct(
        public string $currentStatus,
        public array $bulkBatchStatusOptions,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['currentStatus'] ?? 'new',
            $data['bulkBatchStatusOptions'] ?? [],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response;

class BatchResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description = null,
        public ?array $batchStatus = null,
        public ?array $campaign = null,
        public ?array $campaignIteration = null,
        public ?array $campaignIterationWeek = null,
        public ?int $prospectsCount = 0,
        public ?BatchPricingResponseDTO $batchPricing = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['description'] ?? null,
            $data['batch_status'] ?? null,
            $data['campaign'] ?? null,
            $data['campaign_iteration'] ?? null,
            $data['campaign_iteration_week'] ?? null,
            $data['prospects_count'] ?? 0
        );
    }
}

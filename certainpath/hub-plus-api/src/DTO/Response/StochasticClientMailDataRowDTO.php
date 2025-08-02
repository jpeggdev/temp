<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\DTO\CampaignProduct\CampaignProductResponseDTO;

class StochasticClientMailDataRowDTO
{
    public function __construct(
        public int $id,
        public int $batchNumber,
        public ?string $intacctId,
        public ?string $clientName,
        public ?int $productId,
        public ?int $campaignId,
        public ?string $campaignName,
        public ?string $batchStatus,
        public int $prospectCount,
        public int $week,
        public int $year,
        public ?string $startDate,
        public ?string $endDate,
        public ?CampaignProductResponseDTO $campaignProduct,
        public ?BatchPricingResponseDTO $batchPricing,
        public ?string $referenceString,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            batchNumber: (int) $data['batch_number'],
            intacctId: $data['intacctId'] ?? null,
            clientName: $data['clientName'] ?? null,
            productId: $data['product_id'] ?? null,
            campaignId: $data['campaign_id'] ?? null,
            campaignName: $data['campaign_name'] ?? null,
            batchStatus: $data['batch_status'] ?? null,
            prospectCount: (int) ($data['prospect_count'] ?? 0),
            week: (int) ($data['week'] ?? 0),
            year: (int) ($data['year'] ?? 0),
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            campaignProduct: null,
            batchPricing: null,
            referenceString: $data['reference'] ?? null,
        );
    }
}

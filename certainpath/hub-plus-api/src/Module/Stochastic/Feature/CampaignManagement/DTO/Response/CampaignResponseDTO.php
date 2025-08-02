<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\DTO\Response;

use App\DTO\CampaignProduct\CampaignProductResponseDTO;
use App\DTO\Response\BatchResponseDTO;
use App\DTO\Response\CampaignPricingResponseDTO;

class CampaignResponseDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $companyId = null,
        public ?string $intacctId = null,
        public ?int $productId = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?int $mailingIterationWeeks = null,
        public ?string $phoneNumber = null,
        public ?array $campaignStatus = null,
        public ?array $mailPackage = null,
        public ?array $batches = [],
        public bool $canBeBilled = false,
        public ?CampaignProductResponseDTO $campaignProduct = null,
        public ?CampaignPricingResponseDTO $campaignPricing = null,
    ) {
    }

    public static function fromArrayAsync(): self
    {
        return new self();
    }

    public static function fromArray(array $data): self
    {
        $campaignStatus = !empty($data['campaign_status']) ? [
            'id' => $data['campaign_status']['id'] ?? null,
            'name' => $data['campaign_status']['name'] ?? null,
        ] : null;

        $mailPackage = !empty($data['mail_package']) ? [
            'id' => $data['mail_package']['id'] ?? null,
            'name' => $data['mail_package']['name'] ?? null,
            'series' => $data['mail_package']['series'] ?? null,
            'external_id' => $data['mail_package']['external_id'] ?? null,
            'is_active' => $data['mail_package']['is_active'] ?? null,
            'is_deleted' => $data['mail_package']['is_deleted'] ?? null,
        ] : null;

        $batches = !empty($data['batches']) ? $data['batches'] : [];
        if (!empty($batches)) {
            $batches = array_map(static function (array $batch) {
                return BatchResponseDTO::fromArray($batch);
            }, (array) $batches);
        }

        return new self(
            id: $data['id'],
            companyId: $data['company_id'],
            intacctId: $data['intacct_id'],
            productId: $data['hub_plus_product_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            startDate: $data['start_date'] ?? null,
            endDate: $data['end_date'] ?? null,
            mailingIterationWeeks: $data['mailing_iteration_weeks'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            campaignStatus: $campaignStatus,
            mailPackage: $mailPackage,
            batches: $batches,
            canBeBilled: false,
        );
    }
}

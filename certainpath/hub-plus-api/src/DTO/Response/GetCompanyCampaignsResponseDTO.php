<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Module\Stochastic\Feature\CampaignManagement\DTO\Response\CampaignResponseDTO;

class GetCompanyCampaignsResponseDTO
{
    /**
     * @param CampaignResponseDTO[] $campaigns
     */
    public function __construct(
        public array $campaigns,
        public ?int $total = null,
        public ?int $currentPage = null,
        public ?int $perPage = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $campaignsData = $data['data'] ?? [];
        $meta = $data['meta'] ?? [];

        $campaigns = array_map(fn ($campaignData) => CampaignResponseDTO::fromArray($campaignData), $campaignsData);

        return new self(
            campaigns: $campaigns,
            total: $meta['total'] ?? null,
            currentPage: $meta['currentPage'] ?? null,
            perPage: $meta['perPage'] ?? null
        );
    }
}

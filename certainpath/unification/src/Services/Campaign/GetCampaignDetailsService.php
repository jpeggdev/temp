<?php

namespace App\Services\Campaign;

use App\DTO\Response\Campaign\GetCampaignDetailsResponseDTO;
use App\Exceptions\DomainException\Campaign\FailedToGetCampaignDetailsException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Repository\CampaignRepository;
use App\Services\DetailsMetadata\CampaignDetailsMetadataService;

readonly class GetCampaignDetailsService
{
    public function __construct(
        private CampaignRepository $campaignRepository,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws CampaignNotFoundException
     * @throws FailedToGetCampaignDetailsException
     */
    public function getCampaignDetails(int $campaignId): GetCampaignDetailsResponseDTO
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($campaignId);
        $prospectFilterRules = $campaign->getProspectFilterRules();

        if ($prospectFilterRules->isEmpty()) {
            throw new FailedToGetCampaignDetailsException(
                'Failed to fetch campaign details. Campaign ID: ' . $campaignId
            );
        }

        return GetCampaignDetailsResponseDTO::fromEntity($campaign);
    }
}

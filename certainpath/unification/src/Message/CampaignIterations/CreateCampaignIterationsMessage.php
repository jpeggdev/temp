<?php

namespace App\Message\CampaignIterations;

use App\DTO\Request\Campaign\CreateCampaignDTO;

class CreateCampaignIterationsMessage
{
    public int $campaignId;

    public CreateCampaignDTO $createCampaignDTO;

    public function __construct(
        int $campaignId,
        CreateCampaignDTO $createCampaignDTO
    ) {
        $this->campaignId = $campaignId;
        $this->createCampaignDTO = $createCampaignDTO;
    }
}

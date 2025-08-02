<?php

namespace App\Resources;

use App\Transformers\CampaignStatusTransformer;

class CampaignStatusResource extends AbstractResource
{
    protected function getTransformer(): CampaignStatusTransformer
    {
        return new CampaignStatusTransformer();
    }
}

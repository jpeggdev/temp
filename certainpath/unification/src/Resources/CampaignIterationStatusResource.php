<?php

namespace App\Resources;

use App\Transformers\CampaignIterationStatusTransformer;

class CampaignIterationStatusResource extends AbstractResource
{
    protected function getTransformer(): CampaignIterationStatusTransformer
    {
        return new CampaignIterationStatusTransformer();
    }
}

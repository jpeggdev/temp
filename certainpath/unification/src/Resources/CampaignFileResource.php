<?php

namespace App\Resources;

use App\Transformers\CampaignFileTransformer;

class CampaignFileResource extends AbstractResource
{
    protected function getTransformer(): CampaignFileTransformer
    {
        return new CampaignFileTransformer();
    }
}

<?php

namespace App\Transformers;

use App\Entity\CampaignStatus;
use League\Fractal\TransformerAbstract;

class CampaignStatusTransformer extends TransformerAbstract
{
    public function transform(CampaignStatus $campaignStatus): array
    {
        return [
            'id' => $campaignStatus->getId(),
            'name' => $campaignStatus->getName(),
        ];
    }
}

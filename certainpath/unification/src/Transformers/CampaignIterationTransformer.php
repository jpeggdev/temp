<?php

namespace App\Transformers;

use App\Entity\CampaignIteration;
use League\Fractal\TransformerAbstract;

class CampaignIterationTransformer extends TransformerAbstract
{
    public function transform(CampaignIteration $campaignIteration): array
    {
        return [
            'id' => $campaignIteration->getId(),
            'campaign_id' => $campaignIteration->getCampaign()?->getId(),
            'campaign_iteration_status_id' => $campaignIteration->getCampaignIterationStatus()?->getId(),
            'iteration_number' => $campaignIteration->getIterationNumber(),
            'start_date' => $campaignIteration->getStartDate()?->format('Y-m-d'),
            'end_date' => $campaignIteration->getEndDate()?->format('Y-m-d'),
        ];
    }
}

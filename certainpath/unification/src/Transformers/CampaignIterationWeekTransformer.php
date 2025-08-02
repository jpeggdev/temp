<?php

namespace App\Transformers;

use App\Entity\CampaignIterationWeek;
use League\Fractal\TransformerAbstract;

class CampaignIterationWeekTransformer extends TransformerAbstract
{
    public function transform(CampaignIterationWeek $campaignIterationWeek): array
    {
        $data['id'] = $campaignIterationWeek->getId();
        $data['campaign_iteration_id'] = $campaignIterationWeek->getId();
        $data['week_number'] = $campaignIterationWeek->getWeekNumber();
        $data['start_date'] = $campaignIterationWeek->getStartDate()?->format('Y-m-d');
        $data['end_date'] = $campaignIterationWeek->getEndDate()?->format('Y-m-d');

        return $data;
    }
}

<?php

namespace App\Transformers;

use App\Entity\CampaignIterationStatus;
use League\Fractal\TransformerAbstract;

class CampaignIterationStatusTransformer extends TransformerAbstract
{
    public function transform(CampaignIterationStatus $batch): array
    {
        $data['id'] = $batch->getId();
        $data['name'] = $batch->getName();

        return $data;
    }
}

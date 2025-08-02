<?php

namespace App\Transformers;

use App\Entity\EventStatus;
use League\Fractal\TransformerAbstract;

class EventStatusTransformer extends TransformerAbstract
{
    public function transform(EventStatus $eventStatus): array
    {
        $data['id'] = $eventStatus->getId();
        $data['name'] = $eventStatus->getName();

        return $data;
    }
}

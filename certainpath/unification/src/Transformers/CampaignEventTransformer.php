<?php

namespace App\Transformers;

use App\Entity\CampaignEvent;
use App\Resources\EventStatusResource;
use League\Fractal\TransformerAbstract;

class CampaignEventTransformer extends TransformerAbstract
{
    public function __construct(
        private readonly EventStatusResource $eventStatusResource
    ) {
    }


    public function transform(CampaignEvent $campaignEvent): array
    {
        return [
            'id' => $campaignEvent->getId(),
            'event_status' => $this->includeEventStatus($campaignEvent),
            'error_message' => $campaignEvent->getErrorMessage(),
        ];
    }

    private function includeEventStatus(CampaignEvent $campaignEvent): array
    {
        $eventStatus = $campaignEvent->getEventStatus();
        return $this->eventStatusResource->transformItem($eventStatus);
    }
}

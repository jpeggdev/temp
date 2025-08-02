<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventDiscountManagement\DTO\Response;

use App\DTO\Response\Event\CreateUpdateEventResponseDTO;
use App\Entity\EventDiscount;

abstract class BaseEventDiscountResponseDTO
{
    protected static function prepareDiscountTypeData(EventDiscount $eventDiscount): array
    {
        $discountType = $eventDiscount->getDiscountType();
        if (!$discountType) {
            return [];
        }

        return [
            'id' => $discountType->getId(),
            'name' => $discountType->getName(),
            'displayName' => $discountType->getDisplayName(),
        ];
    }

    protected static function prepareEventsData(EventDiscount $eventDiscount): array
    {
        $eventsData = [];

        $events = $eventDiscount->getEvents();
        if ($events->isEmpty()) {
            return [];
        }

        foreach ($events as $key => $event) {
            $eventsData[$key] = new CreateUpdateEventResponseDTO(
                id: $event->getId(),
                uuid: $event->getUuid(),
                eventCode: $event->getEventCode(),
                eventName: $event->getEventName(),
                thumbnailUrl: $event->getThumbnail()?->getUrl(),
                isPublished: (bool) $event->getIsPublished(),
                isVoucherEligible: $event->isVoucherEligible(),
            );
        }

        return $eventsData;
    }
}

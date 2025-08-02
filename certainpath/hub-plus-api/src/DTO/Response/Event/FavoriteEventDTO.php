<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

use App\Entity\Event;

readonly class FavoriteEventDTO
{
    public function __construct(
        public int $id,
        public string $eventCode,
        public string $eventName,
        public string $eventDescription,
        public float $eventPrice,
    ) {
    }

    public static function fromEntity(Event $event): self
    {
        return new self(
            id: $event->getId(),
            eventCode: $event->getEventCode(),
            eventName: $event->getEventName(),
            eventDescription: $event->getEventDescription(),
            eventPrice: (float) $event->getEventPrice()
        );
    }
}

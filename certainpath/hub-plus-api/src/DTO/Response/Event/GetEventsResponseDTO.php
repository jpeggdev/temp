<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

use App\Entity\Event;

class GetEventsResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $eventCode,
        public string $eventName,
        public string $eventDescription,
        public bool $isPublished,
        public float $eventPrice,
        public ?string $thumbnailUrl,
        public ?string $eventTypeName,
        public ?string $eventCategoryName,
        public ?\DateTimeInterface $createdAt,
    ) {
    }

    public static function fromEntity(Event $event): self
    {
        return new self(
            id: $event->getId(),
            uuid: $event->getUuid(),
            eventCode: $event->getEventCode(),
            eventName: $event->getEventName(),
            eventDescription: $event->getEventDescription(),
            isPublished: (bool) $event->getIsPublished(),
            eventPrice: $event->getEventPrice(),
            thumbnailUrl: $event->getThumbnail()?->getUrl(),
            eventTypeName: $event->getEventTypeName(),
            eventCategoryName: $event->getEventCategoryName(),
            createdAt: $event->getCreatedAt(),
        );
    }
}

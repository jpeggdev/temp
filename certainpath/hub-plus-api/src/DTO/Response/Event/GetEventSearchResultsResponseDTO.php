<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

use App\Entity\Event;

class GetEventSearchResultsResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $eventCode,
        public string $eventName,
        public string $eventDescription,
        public float $eventPrice,
        public bool $isPublished,
        public ?string $thumbnailUrl,
        public ?string $eventTypeName,
        public ?string $eventCategoryName,
        public ?\DateTimeInterface $createdAt,
        public ?int $viewCount,
        public bool $isVoucherEligible = false,
    ) {
    }

    public static function fromEntity(Event $event, ?string $presignedThumbnailUrl = null): self
    {
        return new self(
            id: $event->getId(),
            uuid: $event->getUuid(),
            eventCode: $event->getEventCode(),
            eventName: $event->getEventName(),
            eventDescription: $event->getEventDescription(),
            eventPrice: $event->getEventPrice(),
            isPublished: (bool) $event->getIsPublished(),
            thumbnailUrl: $presignedThumbnailUrl,
            eventTypeName: $event->getEventTypeName(),
            eventCategoryName: $event->getEventCategoryName(),
            createdAt: $event->getCreatedAt(),
            viewCount: $event->getViewCount(),
            isVoucherEligible: (bool) $event->isVoucherEligible()
        );
    }
}

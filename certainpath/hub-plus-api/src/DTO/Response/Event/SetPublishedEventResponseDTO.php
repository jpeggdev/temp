<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

use App\Entity\Event;

class SetPublishedEventResponseDTO
{
    public function __construct(
        public string $uuid,
        public bool $isPublished,
        public string $eventName,
    ) {
    }

    public static function fromEntity(Event $event): self
    {
        return new self(
            $event->getUuid(),
            $event->getIsPublished(),
            $event->getEventName(),
        );
    }
}

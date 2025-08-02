<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

use App\Entity\Event;

class EventLookupResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromEntity(Event $event): self
    {
        return new self($event->getId(), $event->getEventName());
    }
}

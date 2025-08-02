<?php

declare(strict_types=1);

namespace App\DTO\Response\EventType;

use App\Entity\EventType;

readonly class EventTypeResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
    ) {
    }

    public static function fromEntity(EventType $eventType): self
    {
        return new self(
            $eventType->getId(),
            $eventType->getName(),
            $eventType->getDescription(),
        );
    }
}

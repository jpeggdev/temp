<?php

declare(strict_types=1);

namespace App\DTO\Response\EventType;

use App\Entity\EventType;

class EditEventTypeResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
        public bool $isActive,
        public \DateTimeInterface $createdAt,
        public \DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(EventType $eventType): self
    {
        return new self(
            $eventType->getId(),
            $eventType->getName(),
            $eventType->getDescription(),
            $eventType->isActive(),
            $eventType->getCreatedAt(),
            $eventType->getUpdatedAt(),
        );
    }
}

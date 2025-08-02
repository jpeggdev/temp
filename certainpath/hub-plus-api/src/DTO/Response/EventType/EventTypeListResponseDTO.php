<?php

declare(strict_types=1);

namespace App\DTO\Response\EventType;

use App\Entity\EventType;

readonly class EventTypeListResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public bool $isActive,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function fromEntity(EventType $eventType): self
    {
        return new self(
            id: $eventType->getId(),
            name: $eventType->getName(),
            description: $eventType->getDescription(),
            isActive: $eventType->isActive(),
            createdAt: $eventType->getCreatedAt(),
            updatedAt: $eventType->getUpdatedAt(),
        );
    }
}

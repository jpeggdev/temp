<?php

declare(strict_types=1);

namespace App\DTO\Response\EventCategory;

use App\Entity\EventCategory;

readonly class DeleteEventCategoryResponseDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public bool $isActive,
    ) {
    }

    public static function fromEntity(EventCategory $eventCategory): self
    {
        return new self(
            id: $eventCategory->getId(),
            name: $eventCategory->getName(),
            description: $eventCategory->getDescription(),
            isActive: $eventCategory->isActive(),
        );
    }
}

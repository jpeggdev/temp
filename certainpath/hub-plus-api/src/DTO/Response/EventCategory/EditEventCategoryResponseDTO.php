<?php

declare(strict_types=1);

namespace App\DTO\Response\EventCategory;

use App\Entity\EventCategory;

class EditEventCategoryResponseDTO
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

    public static function fromEntity(EventCategory $eventCategory): self
    {
        return new self(
            $eventCategory->getId(),
            $eventCategory->getName(),
            $eventCategory->getDescription(),
            $eventCategory->isActive(),
            $eventCategory->getCreatedAt(),
            $eventCategory->getUpdatedAt(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response\EventCategory;

use App\Entity\EventCategory;

readonly class EventCategoryResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $description,
    ) {
    }

    public static function fromEntity(EventCategory $eventCategory): self
    {
        return new self(
            $eventCategory->getId(),
            $eventCategory->getName(),
            $eventCategory->getDescription(),
        );
    }
}

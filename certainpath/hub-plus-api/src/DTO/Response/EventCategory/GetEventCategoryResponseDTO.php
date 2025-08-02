<?php

declare(strict_types=1);

namespace App\DTO\Response\EventCategory;

use App\Entity\EventCategory;

class GetEventCategoryResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public bool $isActive,
    ) {
    }

    public static function fromEntity(EventCategory $category): self
    {
        return new self(
            id: $category->getId() ?? 0,
            name: $category->getName(),
            description: $category->getDescription(),
            isActive: $category->isActive()
        );
    }

    /**
     * Convert an array of EventCategory entities into an array of DTOs.
     *
     * @param EventCategory[] $categories
     *
     * @return self[]
     */
    public static function fromEntities(array $categories): array
    {
        return array_map(
            static fn (EventCategory $c) => self::fromEntity($c),
            $categories
        );
    }
}

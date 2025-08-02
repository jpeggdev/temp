<?php

declare(strict_types=1);

namespace App\DTO\Response\ResourceCategory;

use App\Entity\ResourceCategory;

class GetResourceCategoriesResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromEntity(ResourceCategory $category): self
    {
        return new self(
            $category->getId(),
            $category->getName()
        );
    }
}

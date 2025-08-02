<?php

declare(strict_types=1);

namespace App\DTO\Response\ResourceCategory;

class CreateUpdateResourceCategoryResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

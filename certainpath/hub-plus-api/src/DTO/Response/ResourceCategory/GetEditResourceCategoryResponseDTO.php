<?php

declare(strict_types=1);

namespace App\DTO\Response\ResourceCategory;

class GetEditResourceCategoryResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
    ) {
    }
}

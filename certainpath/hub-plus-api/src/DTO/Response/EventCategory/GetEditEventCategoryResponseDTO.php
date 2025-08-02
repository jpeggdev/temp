<?php

declare(strict_types=1);

namespace App\DTO\Response\EventCategory;

class GetEditEventCategoryResponseDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public bool $isActive,
    ) {
    }
}

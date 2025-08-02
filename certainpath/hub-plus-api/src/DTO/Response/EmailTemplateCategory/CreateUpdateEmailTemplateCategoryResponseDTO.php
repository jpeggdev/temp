<?php

declare(strict_types=1);

namespace App\DTO\Response\EmailTemplateCategory;

class CreateUpdateEmailTemplateCategoryResponseDTO
{
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $displayedName,
        public ?string $description,
        public ?int $colorId,
        public ?string $colorValue,
    ) {
    }
}

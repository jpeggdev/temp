<?php

declare(strict_types=1);

namespace App\DTO\Response\EmailTemplateCategory;

class GetEditEmailTemplateCategoryResponseDTO
{
    /**
     * @param array<int, array{id: int, value: string}> $availableColors
     */
    public function __construct(
        public ?int $id,
        public ?string $name,
        public ?string $displayedName,
        public ?string $description,
        public ?int $colorId,
        public ?string $colorValue,
        public array $availableColors = [],
    ) {
    }
}

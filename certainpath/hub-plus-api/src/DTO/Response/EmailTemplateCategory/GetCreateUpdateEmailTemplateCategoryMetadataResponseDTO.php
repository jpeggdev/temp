<?php

declare(strict_types=1);

namespace App\DTO\Response\EmailTemplateCategory;

class GetCreateUpdateEmailTemplateCategoryMetadataResponseDTO
{
    /**
     * @param array<int, array{id: int|null, value: string|null}> $colors
     */
    public function __construct(
        public array $colors = [],
    ) {
    }
}

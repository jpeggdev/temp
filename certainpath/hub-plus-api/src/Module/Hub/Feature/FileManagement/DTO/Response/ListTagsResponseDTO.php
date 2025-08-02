<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class ListTagsResponseDTO
{
    /**
     * @param array<TagSummaryDTO> $tags
     */
    public function __construct(
        public array $tags,
        public int $totalCount,
    ) {
    }
}

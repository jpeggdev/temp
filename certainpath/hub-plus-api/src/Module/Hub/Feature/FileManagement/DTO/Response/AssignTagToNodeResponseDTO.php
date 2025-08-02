<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class AssignTagToNodeResponseDTO
{
    public function __construct(
        public int $mappingId,
        public int $tagId,
        public string $tagName,
        public ?string $tagColor,
        public string $filesystemNodeUuid,
        public string $filesystemNodeName,
    ) {
    }
}

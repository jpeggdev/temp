<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class RemoveTagFromNodeResponseDTO
{
    public function __construct(
        public string $message,
        public int $tagId,
        public string $filesystemNodeUuid,
    ) {
    }
}

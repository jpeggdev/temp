<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class CreateAndAssignTagResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $color,
        public int $mappingId,
        public string $filesystemNodeUuid,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}

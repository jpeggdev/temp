<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class RenameTagResponseDTO
{
    public function __construct(
        public int $id,
        public string $oldName,
        public string $newName,
        public ?string $color,
        public string $updatedAt,
    ) {
    }
}

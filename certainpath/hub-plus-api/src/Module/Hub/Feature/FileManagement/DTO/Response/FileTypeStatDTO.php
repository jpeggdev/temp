<?php

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class FileTypeStatDTO
{
    public function __construct(
        public string $type,
        public int $count,
    ) {
    }

    public static function fromTypeWithCount(array $typeData): self
    {
        return new self(
            type: $typeData['file_type'],
            count: (int) $typeData['count'],
        );
    }
}

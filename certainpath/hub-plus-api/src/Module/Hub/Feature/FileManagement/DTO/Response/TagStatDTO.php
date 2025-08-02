<?php

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

readonly class TagStatDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $color,
        public int $count,
    ) {
    }

    public static function fromTagWithCount(array $tagData): self
    {
        return new self(
            id: (int) $tagData['id'],
            name: $tagData['name'],
            color: $tagData['color'],
            count: (int) $tagData['count'],
        );
    }
}

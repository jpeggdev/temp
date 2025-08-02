<?php

namespace App\Module\CraftMigration\DTO\Fields;

class FieldDTO
{
    public function __construct(
        public int $id,
        public int $elementId,
        public string $handle,
        public string $name,
        public int $sortOrder,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? 0,
            elementId: $data['elementId'] ?? 0,
            handle: $data['handle'] ?? '',
            name: $data['name'] ?? '',
            sortOrder: $data['sortOrder'] ?? 0
        );
    }
}

<?php

namespace App\Module\CraftMigration\DTO\Elements;

class ElementDTO
{
    public function __construct(
        public int $id,
        public ?string $slug,
        public ?string $uri,
        public ?int $fieldLayoutId,
        public string $type,
        public string $dateCreated,
        public string $dateUpdated,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            slug: $data['slug'] ?? null,
            uri: $data['uri'] ?? null,
            fieldLayoutId: isset($data['fieldLayoutId']) ? (int) $data['fieldLayoutId'] : null,
            type: $data['type'],
            dateCreated: $data['dateCreated'],
            dateUpdated: $data['dateUpdated']
        );
    }
}

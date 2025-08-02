<?php

namespace App\Module\CraftMigration\DTO\Elements;

class EntryDTO
{
    public function __construct(
        public int $id,
        public int $fieldLayoutId,
        public string $type,
        public ?string $slug,
        public ?string $uri,
        public string $title,
        public string $postDate,
        public string $dateCreated,
        public string $dateUpdated,
        public bool $enabled,
        public array $fields = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        $fields = array_filter($data, function ($key) {
            return str_starts_with($key, 'field_');
        }, ARRAY_FILTER_USE_KEY);

        return new self(
            id: (int) $data['elementId'],
            fieldLayoutId: (int) $data['fieldLayoutId'],
            type: $data['type'],
            slug: $data['slug'] ?? null,
            uri: $data['uri'] ?? null,
            title: $data['title'],
            postDate: $data['postDate'] ?? '',
            dateCreated: $data['dateCreated'],
            dateUpdated: $data['dateUpdated'],
            enabled: (bool) $data['enabled'],
            fields: $fields
        );
    }
}

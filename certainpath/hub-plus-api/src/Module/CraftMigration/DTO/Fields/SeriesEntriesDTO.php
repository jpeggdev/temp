<?php

namespace App\Module\CraftMigration\DTO\Fields;

class SeriesEntriesDTO
{
    public function __construct(
        public string $slug,
        public string $title,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            slug: $data['slug'] ?? '',
            title: $data['title'] ?? ''
        );
    }
}

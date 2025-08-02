<?php

namespace App\Module\CraftMigration\DTO\Fields;

class ResourceFileDTO
{
    public ?string $volumeFilename;

    public function __construct(
        public string $filename,
        public string $path,
        public array $settings,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            filename: $data['filename'] ?? '',
            path: $data['path'] ?? '',
            settings: $data['settings'] ? json_decode($data['settings'], true) : []
        );
    }
}

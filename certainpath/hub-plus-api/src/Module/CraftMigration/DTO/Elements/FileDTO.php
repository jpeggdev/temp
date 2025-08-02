<?php

namespace App\Module\CraftMigration\DTO\Elements;

class FileDTO
{
    public function __construct(
        public ?int $fileId,
        public ?string $baseFilename,
        public string $localFilename,
        public string $remoteFilename,
        public string $volumeFilename,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fileId: $data['fileId'] ?? null,
            baseFilename: $data['baseFilename'] ?? '',
            localFilename: $data['localFilename'] ?? '',
            remoteFilename: $data['remoteFilename'] ?? '',
            volumeFilename: $data['volumeFilename'] ?? ''
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\File;

readonly class ReplaceFileResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $originalName,
        public string $url,
        public string $type,
        public string $mimeType,
        public int $fileSize,
        public string $fileType,
        public string $replacedAt,
    ) {
    }

    public static function fromEntity(File $file): self
    {
        return new self(
            uuid: $file->getUuid(),
            name: $file->getName(),
            originalName: $file->getOriginalFilename() ?? $file->getName(),
            url: $file->getUrl(),
            type: 'file',
            mimeType: $file->getMimeType() ?? 'application/octet-stream',
            fileSize: $file->getFileSize() ?? 0,
            fileType: $file->getFileType(),
            replacedAt: $file->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}

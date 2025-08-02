<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\File;
use App\Entity\FilesystemNode;
use App\Entity\Folder;

readonly class RenameFilesystemNodeResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $type,
        public ?string $parentUuid,
        public string $createdAt,
        public string $updatedAt,
        public ?string $mimeType = null,
        public ?int $fileSize = null,
        public ?string $url = null,
        public ?string $path = null,
    ) {
    }

    public static function fromEntity(FilesystemNode $node): self
    {
        if ($node instanceof Folder) {
            return new self(
                uuid: $node->getUuid(),
                name: $node->getName(),
                type: 'folder',
                parentUuid: $node->getParent()?->getUuid(),
                createdAt: $node->getCreatedAt()->format(\DateTimeInterface::ATOM),
                updatedAt: $node->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                path: $node->getPath(),
            );
        }

        if ($node instanceof File) {
            return new self(
                uuid: $node->getUuid(),
                name: $node->getName(),
                type: 'file',
                parentUuid: $node->getParent()?->getUuid(),
                createdAt: $node->getCreatedAt()->format(\DateTimeInterface::ATOM),
                updatedAt: $node->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                mimeType: $node->getMimeType(),
                fileSize: $node->getFileSize(),
                url: $node->getUrl(),
            );
        }

        return new self(
            uuid: $node->getUuid(),
            name: $node->getName(),
            type: 'unknown',
            parentUuid: $node->getParent()?->getUuid(),
            createdAt: $node->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $node->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}

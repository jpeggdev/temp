<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\Folder;

readonly class FolderInfoDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $type,
        public ?string $parentUuid,
        public string $createdAt,
        public string $updatedAt,
        public string $path,
    ) {
    }

    public static function fromEntity(?Folder $folder): ?self
    {
        if (null === $folder) {
            return null;
        }

        return new self(
            uuid: $folder->getUuid(),
            name: $folder->getName(),
            type: 'folder',
            parentUuid: $folder->getParent()?->getUuid(),
            createdAt: $folder->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $folder->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            path: $folder->getPath(),
        );
    }
}

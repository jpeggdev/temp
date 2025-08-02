<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\Folder;

readonly class FolderResponseDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $path,
        public ?string $parentUuid,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Folder $folder): self
    {
        return new self(
            uuid: $folder->getUuid(),
            name: $folder->getName(),
            path: $folder->getPath(),
            parentUuid: $folder->getParent()?->getUuid(),
            createdAt: $folder->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $folder->getUpdatedAt()->format(\DateTimeInterface::ATOM),
        );
    }
}

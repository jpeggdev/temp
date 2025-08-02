<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\Folder;

readonly class BreadcrumbItemDTO
{
    public function __construct(
        public string $uuid,
        public string $name,
    ) {
    }

    public static function fromEntity(Folder $folder): self
    {
        return new self(
            uuid: $folder->getUuid(),
            name: $folder->getName(),
        );
    }

    /**
     * @param Folder[] $folders
     *
     * @return BreadcrumbItemDTO[]
     */
    public static function fromEntities(array $folders): array
    {
        return array_map(fn (Folder $folder) => self::fromEntity($folder), $folders);
    }
}

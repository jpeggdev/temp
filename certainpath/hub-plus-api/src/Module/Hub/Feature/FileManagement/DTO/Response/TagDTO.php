<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\FileSystemNodeTag;

readonly class TagDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $color,
    ) {
    }

    public static function fromEntity(FileSystemNodeTag $tag): self
    {
        return new self(
            id: $tag->getId(),
            name: $tag->getName(),
            color: $tag->getColor(),
        );
    }

    /**
     * @param FileSystemNodeTag[] $tags
     *
     * @return TagDTO[]
     */
    public static function fromEntities(array $tags): array
    {
        return array_map(fn (FileSystemNodeTag $tag) => self::fromEntity($tag), $tags);
    }
}

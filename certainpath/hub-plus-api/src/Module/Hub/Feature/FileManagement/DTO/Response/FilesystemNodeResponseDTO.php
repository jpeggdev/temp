<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\File;
use App\Entity\FilesystemNode;
use App\Entity\Folder;

readonly class FilesystemNodeResponseDTO
{
    /**
     * @param TagDTO[] $tags
     */
    public function __construct(
        public string $uuid,
        public string $name,
        public string $fileType,
        public string $type,
        public ?string $parentUuid,
        public string $createdAt,
        public string $updatedAt,
        public array $tags = [],
        public ?string $mimeType = null,
        public ?int $fileSize = null,
        public ?string $url = null,
        public ?string $path = null,
    ) {
    }

    public static function fromEntity(FilesystemNode $node): self
    {
        // Extract tags from the node
        $tags = [];
        foreach ($node->getFileSystemNodeTagMappings() as $mapping) {
            $tag = $mapping->getFileSystemNodeTag();
            if ($tag) {
                $tags[] = TagDTO::fromEntity($tag);
            }
        }

        if ($node instanceof Folder) {
            return new self(
                uuid: $node->getUuid(),
                name: $node->getName(),
                fileType: $node->getFileType(),
                type: 'folder',
                parentUuid: $node->getParent()?->getUuid(),
                createdAt: $node->getCreatedAt()->format(\DateTimeInterface::ATOM),
                updatedAt: $node->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                tags: $tags,
                fileSize: $node->getFileSize(),
                path: $node->getPath(),
            );
        }

        if ($node instanceof File) {
            return new self(
                uuid: $node->getUuid(),
                name: $node->getName(),
                fileType: $node->getFileType(),
                type: 'file',
                parentUuid: $node->getParent()?->getUuid(),
                createdAt: $node->getCreatedAt()->format(\DateTimeInterface::ATOM),
                updatedAt: $node->getUpdatedAt()->format(\DateTimeInterface::ATOM),
                tags: $tags,
                mimeType: $node->getMimeType(),
                fileSize: $node->getFileSize(),
                url: $node->getUrl(),
            );
        }

        return new self(
            uuid: $node->getUuid(),
            name: $node->getName(),
            fileType: $node->getFileType(),
            type: 'unknown',
            parentUuid: $node->getParent()?->getUuid(),
            createdAt: $node->getCreatedAt()->format(\DateTimeInterface::ATOM),
            updatedAt: $node->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            tags: $tags,
        );
    }

    /**
     * @param FilesystemNode[] $nodes
     *
     * @return FilesystemNodeResponseDTO[]
     */
    public static function fromEntities(array $nodes): array
    {
        return array_map(fn (FilesystemNode $node) => self::fromEntity($node), $nodes);
    }
}

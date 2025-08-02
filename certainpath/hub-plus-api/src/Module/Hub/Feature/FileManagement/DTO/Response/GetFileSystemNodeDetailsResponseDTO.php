<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\FileManagement\DTO\Response;

use App\Entity\File;
use App\Entity\FilesystemNode;
use App\Entity\Folder;

readonly class GetFileSystemNodeDetailsResponseDTO
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
        public ?array $createdBy = null,
        public ?array $updatedBy = null,
        public ?string $md5Hash = null,
        public ?array $duplicates = null,
        public ?array $usages = null,
        public ?string $presignedUrl = null,
    ) {
    }

    public static function fromEntity(
        FilesystemNode $node,
        ?array $duplicates = null,
        ?array $usages = null,
        ?string $presignedUrl = null,
    ): self {
        // Extract tags from the node
        $tags = [];
        foreach ($node->getFileSystemNodeTagMappings() as $mapping) {
            $tag = $mapping->getFileSystemNodeTag();
            if ($tag) {
                $tags[] = TagDTO::fromEntity($tag);
            }
        }

        // Extract creator information
        $createdBy = null;
        if ($node->getCreatedBy()) {
            $createdBy = [
                'id' => $node->getCreatedBy()->getId(),
                'uuid' => $node->getCreatedBy()->getUuid(),
                'firstName' => $node->getCreatedBy()->getFirstName(),
                'lastName' => $node->getCreatedBy()->getLastName(),
                'email' => $node->getCreatedBy()->getUser()?->getEmail(),
            ];
        }

        // Extract updater information
        $updatedBy = null;
        if ($node->getUpdatedBy()) {
            $updatedBy = [
                'id' => $node->getUpdatedBy()->getId(),
                'uuid' => $node->getUpdatedBy()->getUuid(),
                'firstName' => $node->getUpdatedBy()->getFirstName(),
                'lastName' => $node->getUpdatedBy()->getLastName(),
                'email' => $node->getUpdatedBy()->getUser()?->getEmail(),
            ];
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
                createdBy: $createdBy,
                updatedBy: $updatedBy,
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
                createdBy: $createdBy,
                updatedBy: $updatedBy,
                md5Hash: $node->getMd5Hash(),
                duplicates: $duplicates,
                usages: $usages,
                presignedUrl: $presignedUrl,
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
            createdBy: $createdBy,
            updatedBy: $updatedBy,
        );
    }
}

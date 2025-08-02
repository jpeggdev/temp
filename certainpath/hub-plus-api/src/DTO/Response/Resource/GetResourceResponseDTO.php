<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

class GetResourceResponseDTO
{
    /**
     * @param array<mixed> $tagIds
     * @param array<mixed> $tradeIds
     * @param array<mixed> $roleIds
     * @param array<mixed> $categoryIds
     * @param array<mixed> $contentBlocks
     * @param array<mixed> $tags
     * @param array<mixed> $categories
     * @param array<mixed> $trades
     * @param array<mixed> $roles
     * @param array<mixed> $relatedResources
     */
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public ?string $title,
        public ?string $slug,
        public ?string $description,
        public ?string $tagline,
        public ?string $contentUrl,
        public ?string $thumbnailUrl,
        public ?int $thumbnailFileId,
        public ?string $thumbnailFileUuid,
        public bool $isPublished,
        public ?string $publishStartDate,
        public ?string $publishEndDate,
        public int $typeId,
        public ?string $icon,
        public ?string $primaryIcon,
        public ?string $backgroundColor = null,
        public ?string $textColor = null,
        public ?string $borderColor = null,
        public array $tagIds = [],
        public array $tradeIds = [],
        public array $roleIds = [],
        public array $categoryIds = [],
        public array $contentBlocks = [],
        public array $tags = [],
        public array $categories = [],
        public ?string $typeName = null,
        public int $viewCount = 0,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public array $trades = [],
        public array $roles = [],
        public bool $isFavorited = false,
        public array $relatedResources = [],
        public ?string $legacyUrl = null,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

class GetResourceDetailsResponseDTO
{
    /**
     * @param array<array{id: int, name: string}> $categories
     * @param array<array{id: int, name: string}> $trades
     * @param array<array{id: int, name: string}> $roles
     * @param array<array{id: int, name: string}> $tags
     * @param array<array{id: string, type: string, content: string, order_number: int, fileId: ?int, fileUuid?: ?string, fileUrl?: ?string, title: ?string, short_description: ?string}> $contentBlocks
     * @param array<array{title: string, slug: string, description: string, thumbnailUrl: ?string, thumbnailFileUuid?: ?string, primaryIcon: ?string, resourceType: ?string, createdOrPublishStartDate: ?\DateTimeInterface, viewCount: ?int, backgroundColor: ?string, textColor: ?string, borderColor: ?string}> $relatedResources
     */
    public function __construct(
        public int $id,
        public string $uuid,
        public string $title,
        public string $slug,
        public string $description,
        public ?string $tagline,
        public ?string $contentUrl,
        public ?string $filename,
        public ?string $thumbnailUrl,
        public ?string $thumbnailFileUuid,
        public ?string $typeName,
        public ?string $icon,
        public ?string $primaryIcon,
        public ?string $backgroundColor,
        public ?string $textColor,
        public ?string $borderColor,
        public ?int $viewCount,
        public ?string $publishStartDate,
        public string $createdAt,
        public string $updatedAt,
        public ?string $legacyUrl,
        public array $categories,
        public array $trades,
        public array $roles,
        public array $tags,
        public array $contentBlocks,
        public bool $isFavorited,
        public array $relatedResources,
    ) {
    }
}

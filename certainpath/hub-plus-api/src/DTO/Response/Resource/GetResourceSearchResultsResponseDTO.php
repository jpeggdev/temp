<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

use App\Entity\Resource;

class GetResourceSearchResultsResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $title,
        public string $slug,
        public string $description,
        public bool $isPublished,
        public ?string $thumbnailUrl,
        public ?string $thumbnailFileUuid,
        public ?string $primaryIcon,
        public bool $isFeatured,
        public ?string $resourceType,
        public ?\DateTimeInterface $createdOrPublishStartDate,
        public ?int $viewCount,
        public ?string $backgroundColor,
        public ?string $textColor,
        public ?string $borderColor,
    ) {
    }

    public static function fromEntity(Resource $resource, ?string $presignedThumbnailUrl = null): self
    {
        $publishOrCreated = $resource->getPublishStartDate() ?? $resource->getCreatedAt();
        $type = $resource->getType();
        $thumbnailFile = $resource->getThumbnail();

        return new self(
            id: $resource->getId(),
            uuid: $resource->getUuid(),
            title: $resource->getTitle(),
            slug: $resource->getSlug(),
            description: $resource->getDescription(),
            isPublished: $resource->isPublished() ?? false,
            thumbnailUrl: $presignedThumbnailUrl,
            thumbnailFileUuid: $thumbnailFile?->getUuid(),
            primaryIcon: $type?->getPrimaryIcon(),
            isFeatured: $resource->isFeatured() ?? false,
            resourceType: $type?->getName(),
            createdOrPublishStartDate: $publishOrCreated,
            viewCount: $resource->getViewCount(),
            backgroundColor: $type?->getBackgroundColor(),
            textColor: $type?->getTextColor(),
            borderColor: $type?->getBorderColor(),
        );
    }
}

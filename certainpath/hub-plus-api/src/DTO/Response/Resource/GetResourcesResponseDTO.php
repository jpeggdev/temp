<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

use App\Entity\Resource;

class GetResourcesResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $title,
        public string $description,
        public bool $isPublished,
        public ?string $thumbnailUrl,
        public ?string $primaryIcon,
        public bool $isFeatured,
        public ?string $resourceType,
        public ?\DateTimeInterface $createdAt,
    ) {
    }

    public static function fromEntity(Resource $resource): self
    {
        $type = $resource->getType();

        return new self(
            id: $resource->getId(),
            uuid: $resource->getUuid(),
            title: $resource->getTitle(),
            description: $resource->getDescription(),
            isPublished: $resource->isPublished() ?? false,
            thumbnailUrl: $resource->getThumbnailUrl(),
            primaryIcon: $type?->getPrimaryIcon(),
            isFeatured: $resource->isFeatured() ?? false,
            resourceType: $resource->getType()?->getName(),
            createdAt: $resource->getCreatedAt()
        );
    }
}

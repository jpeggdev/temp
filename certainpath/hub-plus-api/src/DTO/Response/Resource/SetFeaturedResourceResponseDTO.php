<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

use App\Entity\Resource;

class SetFeaturedResourceResponseDTO
{
    public function __construct(
        public string $uuid,
        public bool $isFeatured,
        public string $title,
    ) {
    }

    public static function fromEntity(Resource $resource): self
    {
        return new self(
            $resource->getUuid(),
            $resource->isFeatured() ?? false,
            $resource->getTitle() ?? ''
        );
    }
}

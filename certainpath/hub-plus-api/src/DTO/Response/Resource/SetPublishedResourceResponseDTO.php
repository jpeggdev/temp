<?php

declare(strict_types=1);

namespace App\DTO\Response\Resource;

use App\Entity\Resource;

class SetPublishedResourceResponseDTO
{
    public function __construct(
        public string $uuid,
        public bool $isPublished,
        public string $title,
    ) {
    }

    public static function fromEntity(Resource $resource): self
    {
        return new self(
            $resource->getUuid(),
            $resource->isPublished() ?? false,
            $resource->getTitle() ?? ''
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Module\Hub\Feature\ResourceLibrary\DTO;

use App\Entity\ResourceType;

class GetResourceTypeResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public int $resourceCount,
        public ?string $icon,
    ) {
    }

    public static function fromEntity(ResourceType $resourceType): self
    {
        return new self(
            $resourceType->getId(),
            $resourceType->getName(),
            $resourceType->getResources()->count(),
            $resourceType->getIcon(),
        );
    }
}

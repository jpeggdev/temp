<?php

namespace App\Module\CraftMigration\DTO;

use App\DTO\Request\Resource\CreateUpdateResourceDTO;

class NewResourceDTO
{
    public function __construct(
        public ?int $resourceId = null,
        public ?CreateUpdateResourceDTO $createUpdateResourceDTO = null,
        public ?int $elementId = null,
        public array $relatedElementIds = [],
    ) {
    }

    public static function fromEntity(array $data): self
    {
        return new self(
            resourceId: $data['resourceId'] ?? null,
            createUpdateResourceDTO: $data['createUpdateResourceDTO'] ?? null,
            elementId: $data['elementId'] ?? null,
            relatedElementIds: $data['relatedElementIds'] ?? []
        );
    }
}

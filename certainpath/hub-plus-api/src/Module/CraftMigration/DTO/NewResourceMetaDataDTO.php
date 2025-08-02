<?php

namespace App\Module\CraftMigration\DTO;

// This class is used to reduce the amount of memory used when processing related resources.
class NewResourceMetaDataDTO
{
    public function __construct(
        public ?int $resourceId = null,
        public ?string $slug = null,
        public ?int $elementId = null,
        public array $relatedElementIds = [],
        public array $relatedResourceIds = [],
    ) {
    }

    public static function fromNewResource(NewResourceDTO $data): self
    {
        return new self(
            resourceId: $data->resourceId,
            slug: $data->createUpdateResourceDTO?->slug,
            elementId: $data->elementId,
            relatedElementIds: $data->relatedElementIds,
            relatedResourceIds: $data->createUpdateResourceDTO?->relatedResourceIds ?? []
        );
    }
}

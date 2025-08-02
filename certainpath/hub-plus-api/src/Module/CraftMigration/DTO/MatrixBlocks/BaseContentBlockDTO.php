<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class BaseContentBlockDTO
{
    public function __construct(
        public int $id = 0,
        public int $entryId = 0,
        public int $typeId = 0,
        public int $sortOrder = 0,
        public int $fieldId = 0,
        public ?int $fileId = null,
        public ?string $content = null,
        public ?string $shortDescription = null,
        public ?string $title = null,
        public string $typeName = '',
        public string $typeHandle = '',
        public ?string $resourceType = null,
    ) {
    }

    public static function fromArray(array $data): BaseContentBlockDTO
    {
        return new self(
            id: $data['id'] ?? 0,
            entryId: $data['entryId'] ?? 0,
            typeId: $data['typeId'] ?? 0,
            sortOrder: $data['sortOrder'] ?? 0,
            fieldId: $data['fieldId'] ?? 0,
            fileId: $data['fileId'] ?? null,
            content: $data['content'] ?? null,
            shortDescription: $data['shortDescription'] ?? null,
            title: $data['title'] ?? null,
            typeName: $data['typeName'] ?? '',
            typeHandle: $data['typeHandle'] ?? '',
            resourceType: $data['resourceType'] ?? null
        );
    }

    public function getType(): string
    {
        return 'text';
    }
}

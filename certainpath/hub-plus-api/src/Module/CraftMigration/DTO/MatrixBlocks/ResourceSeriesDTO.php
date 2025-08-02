<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class ResourceSeriesDTO extends BaseContentBlockDTO
{
    public function __construct(
        public array $tradeCategories = [],
        mixed ...$data,
    ) {
        parent::__construct(...$data);
    }

    public static function fromArray(array $data): self
    {
        $parent = parent::fromArray($data);

        return new self(
            tradeCategories: $data['tradeCategories'] ?? [],
            id: $parent->id,
            entryId: $parent->entryId,
            typeId: $parent->typeId,
            sortOrder: $parent->sortOrder,
            fieldId: $parent->fieldId,
            fileId: $parent->fileId,
            content: $parent->content,
            shortDescription: $parent->shortDescription,
            title: $parent->title,
            typeName: $parent->typeName,
            typeHandle: $parent->typeHandle,
            resourceType: $parent->resourceType
        );
    }

    public function getType(): string
    {
        return 'link';
    }
}

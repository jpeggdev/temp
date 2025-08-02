<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class ColumnContentDTO extends BaseContentBlockDTO
{
    public static function fromArray(array $data): self
    {
        if (!empty($data['field_columnContent_leftColumn']) && !empty($data['field_columnContent_rightColumn'])) {
            $data['content'] = sprintf(
                '[%s] [%s]',
                $data['field_columnContent_leftColumn'],
                $data['field_columnContent_rightColumn']
            );
        }

        $parent = parent::fromArray($data);

        return new self(
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
}

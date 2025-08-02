<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class HeadingDTO extends BaseContentBlockDTO
{
    public static function fromArray(array $data): self
    {
        if (!empty($data['field_heading_style']) && !empty($data['field_heading_heading'])) {
            $data['content'] = sprintf(
                '<%s>%s</%s>',
                $data['field_heading_style'],
                $data['field_heading_heading'],
                $data['field_heading_style']
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
            resourceType: $parent->resourceType,
        );
    }
}

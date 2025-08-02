<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class ResourceCourseDTO extends BaseContentBlockDTO
{
    public array $tradeCategories = [];

    public function getType(): string
    {
        return 'link';
    }
}

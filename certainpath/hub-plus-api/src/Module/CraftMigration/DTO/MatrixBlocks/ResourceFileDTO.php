<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class ResourceFileDTO extends BaseContentBlockDTO
{
    public function getType(): string
    {
        return 'file';
    }
}

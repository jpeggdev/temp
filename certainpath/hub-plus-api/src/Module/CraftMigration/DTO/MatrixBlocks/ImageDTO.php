<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class ImageDTO extends BaseContentBlockDTO
{
    public function getType(): string
    {
        return 'image';
    }
}

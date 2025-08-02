<?php

namespace App\Module\CraftMigration\DTO\MatrixBlocks;

class EntryCardDTO extends BaseContentBlockDTO
{
    public function getType(): string
    {
        return 'link';
    }
}

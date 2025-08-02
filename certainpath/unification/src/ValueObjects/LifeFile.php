<?php

namespace App\ValueObjects;

use App\Entity\Trade;

class LifeFile
{
    public function __construct(
        public string $fileName,
        public Trade $trade
    ) {
    }
}

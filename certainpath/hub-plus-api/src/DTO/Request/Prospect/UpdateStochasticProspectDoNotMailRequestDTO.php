<?php

declare(strict_types=1);

namespace App\DTO\Request\Prospect;

class UpdateStochasticProspectDoNotMailRequestDTO
{
    public function __construct(
        public bool $doNotMail,
    ) {
    }
}

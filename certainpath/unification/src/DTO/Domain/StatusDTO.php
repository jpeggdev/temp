<?php

namespace App\DTO\Domain;

class StatusDTO
{
    public function __construct(
        public array $statusKeyValues = [],
    ) {
    }

    public function equals(StatusDTO $compare): bool
    {
        $currentStatus = $this->statusKeyValues;
        $referenceStatus = $compare->statusKeyValues;
        return $currentStatus === $referenceStatus;
    }
}

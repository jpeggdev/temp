<?php

namespace App\Message;

readonly class CompanyFieldServiceImportCreated
{
    public function __construct(
        private int $importId,
    ) {
    }

    public function getImportId(): int
    {
        return $this->importId;
    }
}

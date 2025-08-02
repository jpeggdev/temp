<?php

namespace App\Exceptions\DomainException\Batch;

use App\Exceptions\AppException;

class BatchCannotBeArchivedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "The selected batch cannot be archived.";
    }
}

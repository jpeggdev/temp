<?php

namespace App\Exceptions\DomainException\Batch;

use App\Exceptions\AppException;

class BulkUpdateBatchesStatusesCannotBeCompletedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to bulk update batches status.";
    }
}

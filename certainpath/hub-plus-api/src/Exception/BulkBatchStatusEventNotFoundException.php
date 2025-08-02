<?php

namespace App\Exception;

class BulkBatchStatusEventNotFoundException extends UnificationAPIException
{
    public function __construct(
        string $message = 'No bulk batch status event found.',
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}

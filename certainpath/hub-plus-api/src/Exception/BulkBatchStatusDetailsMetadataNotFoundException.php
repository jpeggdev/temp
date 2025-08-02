<?php

namespace App\Exception;

class BulkBatchStatusDetailsMetadataNotFoundException extends UnificationAPIException
{
    public function __construct(
        string $message = 'Bulk batch status details metadata not found.',
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}

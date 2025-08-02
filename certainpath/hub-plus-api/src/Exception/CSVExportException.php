<?php

namespace App\Exception;

class CSVExportException extends UnificationAPIException
{
    public function __construct(int $batchId, ?\Exception $previous = null)
    {
        $message = "Failed to export CSV for batch ID {$batchId}.";
        parent::__construct($message, 0, $previous);
    }
}

<?php

namespace App\Exception;

class BatchStatusesNotFoundException extends UnificationAPIException
{
    public function __construct(string $message = 'No batch statuses found.', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

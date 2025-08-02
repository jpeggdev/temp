<?php

namespace App\Exception;

class CompanyStatusRetrievalException extends UnificationAPIException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Failed to retrieve status for Company.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

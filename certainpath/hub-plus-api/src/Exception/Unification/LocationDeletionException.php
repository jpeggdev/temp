<?php

namespace App\Exception\Unification;

use App\Exception\UnificationAPIException;

class LocationDeletionException extends UnificationAPIException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Failed to delete location.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

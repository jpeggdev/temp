<?php

namespace App\Exception;

class TagsRetrievalException extends UnificationAPIException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Failed to retrieve Tags.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

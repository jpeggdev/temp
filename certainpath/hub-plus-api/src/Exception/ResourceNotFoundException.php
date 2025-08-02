<?php

declare(strict_types=1);

namespace App\Exception;

class ResourceNotFoundException extends \RuntimeException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Resource not found.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

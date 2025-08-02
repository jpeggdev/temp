<?php

declare(strict_types=1);

namespace App\Exception;

class ResourceCreateUpdateException extends \RuntimeException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Failed to create resource.',
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

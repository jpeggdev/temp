<?php

declare(strict_types=1);

namespace App\Exception;

class UnauthorizedEventTypeOperationException extends AppException
{
    public function __construct(
        private readonly string $operation,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getDefaultMessage(): string
    {
        return sprintf('You are not authorized to %s an event type', $this->operation);
    }
}

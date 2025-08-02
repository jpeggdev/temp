<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

/**
 * Represents a connection test response from the ServiceTitan API
 */
class ConnectionTestResponse
{
    public function __construct(
        private readonly bool $successful,
        private readonly int $statusCode,
        private readonly string $message
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function isFailed(): bool
    {
        return !$this->successful;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

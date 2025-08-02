<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

/**
 * Represents a response from the ServiceTitan API
 */
class ApiResponse
{
    public function __construct(
        private readonly bool $success,
        /** @var array<string, mixed> */
        private readonly array $data,
        private readonly int $statusCode,
        private readonly ?string $error = null
    ) {
    }

    /**
     * Create a successful API response
     *
     * @param array<string, mixed> $data
     */
    public static function success(array $data, int $statusCode = 200): self
    {
        return new self(true, $data, $statusCode);
    }

    /**
     * Create an error API response
     */
    public static function error(string $error, int $statusCode = 500): self
    {
        return new self(false, [], $statusCode, $error);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}

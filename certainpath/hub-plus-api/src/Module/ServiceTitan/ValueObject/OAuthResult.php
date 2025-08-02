<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

readonly class OAuthResult
{
    public function __construct(
        public bool $success,
        public ?string $accessToken = null,
        public ?string $refreshToken = null,
        public ?\DateTimeInterface $expiresAt = null,
        public ?string $error = null,
        public ?string $errorDescription = null,
    ) {
    }

    public static function success(
        string $accessToken,
        string $refreshToken,
        \DateTimeInterface $expiresAt
    ): self {
        return new self(
            success: true,
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: $expiresAt
        );
    }

    public static function failure(string $error, ?string $errorDescription = null): self
    {
        return new self(
            success: false,
            error: $error,
            errorDescription: $errorDescription
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }
}

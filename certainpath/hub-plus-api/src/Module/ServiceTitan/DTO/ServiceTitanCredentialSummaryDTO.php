<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\DTO;

use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use JsonSerializable;

class ServiceTitanCredentialSummaryDTO implements JsonSerializable
{
    public function __construct(
        private readonly string $uuid,
        private readonly ServiceTitanEnvironment $environment,
        private readonly ServiceTitanConnectionStatus $connectionStatus,
        private readonly ?\DateTimeInterface $lastConnectionAttempt,
        private readonly ?\DateTimeInterface $tokenExpiresAt,
        private readonly bool $hasValidCredentials,
        private readonly bool $hasValidTokens,
        private readonly bool $isActiveConnection,
    ) {
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getEnvironment(): ServiceTitanEnvironment
    {
        return $this->environment;
    }

    public function getConnectionStatus(): ServiceTitanConnectionStatus
    {
        return $this->connectionStatus;
    }

    public function getLastConnectionAttempt(): ?\DateTimeInterface
    {
        return $this->lastConnectionAttempt;
    }

    public function getTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->tokenExpiresAt;
    }

    public function hasValidCredentials(): bool
    {
        return $this->hasValidCredentials;
    }

    public function hasValidTokens(): bool
    {
        return $this->hasValidTokens;
    }

    public function isActiveConnection(): bool
    {
        return $this->isActiveConnection;
    }

    public function jsonSerialize(): array
    {
        return [
            'uuid' => $this->uuid,
            'environment' => $this->environment->value,
            'connectionStatus' => $this->connectionStatus->value,
            'lastConnectionAttempt' => $this->lastConnectionAttempt?->format(\DateTimeInterface::ATOM),
            'tokenExpiresAt' => $this->tokenExpiresAt?->format(\DateTimeInterface::ATOM),
            'hasValidCredentials' => $this->hasValidCredentials,
            'hasValidTokens' => $this->hasValidTokens,
            'isActiveConnection' => $this->isActiveConnection,
        ];
    }
}

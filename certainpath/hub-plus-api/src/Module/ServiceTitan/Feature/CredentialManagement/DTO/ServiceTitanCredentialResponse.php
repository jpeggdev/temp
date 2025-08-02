<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\CredentialManagement\DTO;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;

class ServiceTitanCredentialResponse
{
    public string $id;
    public string $environment;
    public string $clientId;
    public string $clientSecret;
    public string $connectionStatus;
    public ?\DateTimeInterface $lastConnectionAttempt;
    public ?\DateTimeInterface $createdAt;
    public ?\DateTimeInterface $updatedAt;

    public function __construct(ServiceTitanCredential $credential)
    {
        $this->id = (string) $credential->getId();
        $this->environment = $credential->getEnvironment()?->value ?? '';
        $this->clientId = $this->maskCredential($credential->getClientId() ?? '');
        $this->clientSecret = $this->maskCredential($credential->getClientSecret() ?? '');
        $this->connectionStatus = $credential->getConnectionStatus()->value;
        $this->lastConnectionAttempt = $credential->getLastConnectionAttempt();
        $this->createdAt = $credential->getCreatedAt();
        $this->updatedAt = $credential->getUpdatedAt();
    }

    private function maskCredential(string $credential): string
    {
        if (strlen($credential) <= 8) {
            return str_repeat('*', strlen($credential));
        }

        return substr($credential, 0, 4).str_repeat('*', strlen($credential) - 8).substr($credential, -4);
    }
}

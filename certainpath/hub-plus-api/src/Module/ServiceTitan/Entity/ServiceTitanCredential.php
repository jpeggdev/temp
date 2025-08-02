<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Entity;

use App\Entity\Company;
use App\Entity\Trait\UuidTrait;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ServiceTitanCredentialRepository::class)]
#[ORM\Table(name: 'servicetitan_credentials')]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(
    name: 'unique_company_environment',
    columns: ['company_id', 'environment']
)]
class ServiceTitanCredential
{
    use TimestampableEntity;
    use UuidTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Company $company = null;

    #[ORM\Column(type: Types::STRING, enumType: ServiceTitanEnvironment::class)]
    private ?ServiceTitanEnvironment $environment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $clientId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $clientSecret = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $accessToken = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $tokenExpiresAt = null;

    #[ORM\Column(type: Types::STRING, enumType: ServiceTitanConnectionStatus::class)]
    private ServiceTitanConnectionStatus $connectionStatus = ServiceTitanConnectionStatus::INACTIVE;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastConnectionAttempt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getEnvironment(): ?ServiceTitanEnvironment
    {
        return $this->environment;
    }

    public function setEnvironment(ServiceTitanEnvironment $environment): static
    {
        $this->environment = $environment;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): static
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getTokenExpiresAt(): ?\DateTimeInterface
    {
        return $this->tokenExpiresAt;
    }

    public function setTokenExpiresAt(?\DateTimeInterface $tokenExpiresAt): static
    {
        $this->tokenExpiresAt = $tokenExpiresAt;

        return $this;
    }

    public function getConnectionStatus(): ServiceTitanConnectionStatus
    {
        return $this->connectionStatus;
    }

    public function setConnectionStatus(ServiceTitanConnectionStatus $connectionStatus): static
    {
        $this->connectionStatus = $connectionStatus;

        return $this;
    }

    public function getLastConnectionAttempt(): ?\DateTimeInterface
    {
        return $this->lastConnectionAttempt;
    }

    public function setLastConnectionAttempt(?\DateTimeInterface $lastConnectionAttempt): static
    {
        $this->lastConnectionAttempt = $lastConnectionAttempt;

        return $this;
    }

    public function isTokenExpired(): bool
    {
        if ($this->tokenExpiresAt === null) {
            return true;
        }

        return $this->tokenExpiresAt <= new \DateTime();
    }

    public function hasValidCredentials(): bool
    {
        return $this->clientId !== null
            && $this->clientSecret !== null
            && trim($this->clientId) !== ''
            && trim($this->clientSecret) !== '';
    }

    public function hasValidTokens(): bool
    {
        return $this->accessToken !== null
            && trim($this->accessToken) !== ''
            && !$this->isTokenExpired();
    }

    public function isActiveConnection(): bool
    {
        return $this->connectionStatus === ServiceTitanConnectionStatus::ACTIVE
            && $this->hasValidCredentials()
            && $this->hasValidTokens();
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\DTO;

use App\Module\ServiceTitan\DTO\ServiceTitanCredentialSummaryDTO;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use PHPUnit\Framework\TestCase;

class ServiceTitanCredentialSummaryDTOTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        // Arrange
        $uuid = 'test-uuid-123';
        $environment = ServiceTitanEnvironment::PRODUCTION;
        $connectionStatus = ServiceTitanConnectionStatus::ACTIVE;
        $lastConnectionAttempt = new \DateTime();
        $tokenExpiresAt = new \DateTime('+1 hour');
        $hasValidCredentials = true;
        $hasValidTokens = true;
        $isActiveConnection = true;

        // Act
        $dto = new ServiceTitanCredentialSummaryDTO(
            $uuid,
            $environment,
            $connectionStatus,
            $lastConnectionAttempt,
            $tokenExpiresAt,
            $hasValidCredentials,
            $hasValidTokens,
            $isActiveConnection
        );

        // Assert
        self::assertSame($uuid, $dto->getUuid());
        self::assertSame($environment, $dto->getEnvironment());
        self::assertSame($connectionStatus, $dto->getConnectionStatus());
        self::assertSame($lastConnectionAttempt, $dto->getLastConnectionAttempt());
        self::assertSame($tokenExpiresAt, $dto->getTokenExpiresAt());
        self::assertSame($hasValidCredentials, $dto->hasValidCredentials());
        self::assertSame($hasValidTokens, $dto->hasValidTokens());
        self::assertSame($isActiveConnection, $dto->isActiveConnection());
    }

    public function testConstructorWithNullDates(): void
    {
        // Act
        $dto = new ServiceTitanCredentialSummaryDTO(
            'uuid-456',
            ServiceTitanEnvironment::INTEGRATION,
            ServiceTitanConnectionStatus::INACTIVE,
            null,
            null,
            false,
            false,
            false
        );

        // Assert
        self::assertNull($dto->getLastConnectionAttempt());
        self::assertNull($dto->getTokenExpiresAt());
        self::assertFalse($dto->hasValidCredentials());
        self::assertFalse($dto->hasValidTokens());
        self::assertFalse($dto->isActiveConnection());
    }

    public function testJsonSerialize(): void
    {
        // Arrange
        $lastConnectionAttempt = new \DateTime('2023-01-01 12:00:00');
        $tokenExpiresAt = new \DateTime('2023-01-01 13:00:00');

        $dto = new ServiceTitanCredentialSummaryDTO(
            'uuid-789',
            ServiceTitanEnvironment::PRODUCTION,
            ServiceTitanConnectionStatus::ACTIVE,
            $lastConnectionAttempt,
            $tokenExpiresAt,
            true,
            true,
            true
        );

        // Act
        $result = $dto->jsonSerialize();

        // Assert
        $expected = [
            'uuid' => 'uuid-789',
            'environment' => 'production',
            'connectionStatus' => 'active',
            'lastConnectionAttempt' => '2023-01-01T12:00:00+00:00',
            'tokenExpiresAt' => '2023-01-01T13:00:00+00:00',
            'hasValidCredentials' => true,
            'hasValidTokens' => true,
            'isActiveConnection' => true,
        ];

        self::assertSame($expected, $result);
    }

    public function testJsonSerializeWithNullDates(): void
    {
        // Arrange
        $dto = new ServiceTitanCredentialSummaryDTO(
            'uuid-null',
            ServiceTitanEnvironment::INTEGRATION,
            ServiceTitanConnectionStatus::INACTIVE,
            null,
            null,
            false,
            false,
            false
        );

        // Act
        $result = $dto->jsonSerialize();

        // Assert
        self::assertNull($result['lastConnectionAttempt']);
        self::assertNull($result['tokenExpiresAt']);
        self::assertSame('integration', $result['environment']);
        self::assertSame('inactive', $result['connectionStatus']);
    }
}

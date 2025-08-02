<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\DTO;

use App\Module\ServiceTitan\DTO\ServiceTitanAlertDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanCredentialSummaryDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanDashboardDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanMetricsDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanSyncSummaryDTO;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use PHPUnit\Framework\TestCase;

class ServiceTitanDashboardDTOTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        // Arrange
        $credentialSummary = new ServiceTitanCredentialSummaryDTO(
            'test-uuid',
            ServiceTitanEnvironment::PRODUCTION,
            ServiceTitanConnectionStatus::ACTIVE,
            new \DateTime(),
            new \DateTime('+1 hour'),
            true,
            true,
            true
        );

        $alert = new ServiceTitanAlertDTO(
            'alert-id',
            ServiceTitanAlertDTO::TYPE_CONNECTION,
            ServiceTitanAlertDTO::SEVERITY_WARNING,
            'Test Alert',
            'Test message',
            new \DateTime()
        );

        $syncSummary = new ServiceTitanSyncSummaryDTO(
            10,
            8,
            2,
            0,
            new \DateTime(),
            null,
            90.0,
            []
        );

        $metrics = new ServiceTitanMetricsDTO(
            100,
            50,
            20,
            10,
            5.0,
            3.0,
            [],
            []
        );

        $lastUpdated = new \DateTime();

        // Act
        $dto = new ServiceTitanDashboardDTO(
            [$credentialSummary],
            [$alert],
            $syncSummary,
            $metrics,
            $lastUpdated
        );

        // Assert
        self::assertSame([$credentialSummary], $dto->getCredentials());
        self::assertSame([$alert], $dto->getAlerts());
        self::assertSame($syncSummary, $dto->getSyncSummary());
        self::assertSame($metrics, $dto->getMetrics());
        self::assertSame($lastUpdated, $dto->getLastUpdated());
    }

    public function testJsonSerialize(): void
    {
        // Arrange
        $credentialSummary = new ServiceTitanCredentialSummaryDTO(
            'test-uuid',
            ServiceTitanEnvironment::PRODUCTION,
            ServiceTitanConnectionStatus::ACTIVE,
            new \DateTime('2023-01-01 12:00:00'),
            new \DateTime('2023-01-01 13:00:00'),
            true,
            true,
            true
        );

        $alert = new ServiceTitanAlertDTO(
            'alert-id',
            ServiceTitanAlertDTO::TYPE_CONNECTION,
            ServiceTitanAlertDTO::SEVERITY_WARNING,
            'Test Alert',
            'Test message',
            new \DateTime('2023-01-01 12:00:00')
        );

        $syncSummary = new ServiceTitanSyncSummaryDTO(
            10,
            8,
            2,
            0,
            new \DateTime('2023-01-01 12:00:00'),
            null,
            90.0,
            []
        );

        $metrics = new ServiceTitanMetricsDTO(
            100,
            50,
            20,
            10,
            5.0,
            3.0,
            [],
            []
        );

        $lastUpdated = new \DateTime('2023-01-01 12:00:00');

        $dto = new ServiceTitanDashboardDTO(
            [$credentialSummary],
            [$alert],
            $syncSummary,
            $metrics,
            $lastUpdated
        );

        // Act
        $result = $dto->jsonSerialize();

        // Assert
        self::assertIsArray($result);
        self::assertArrayHasKey('credentials', $result);
        self::assertArrayHasKey('alerts', $result);
        self::assertArrayHasKey('syncSummary', $result);
        self::assertArrayHasKey('metrics', $result);
        self::assertArrayHasKey('lastUpdated', $result);
        self::assertSame('2023-01-01T12:00:00+00:00', $result['lastUpdated']);
    }
}

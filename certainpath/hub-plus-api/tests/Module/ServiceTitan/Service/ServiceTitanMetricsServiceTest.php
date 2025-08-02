<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\DTO\ServiceTitanDashboardDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanMetricsDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanSyncSummaryDTO;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use App\Module\ServiceTitan\Service\ServiceTitanMetricsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ServiceTitanMetricsServiceTest extends TestCase
{
    private ServiceTitanMetricsService $service;
    private ServiceTitanCredentialRepository|MockObject $credentialRepository;
    private ServiceTitanSyncLogRepository|MockObject $syncLogRepository;
    private CacheItemPoolInterface|MockObject $cache;

    protected function setUp(): void
    {
        $this->credentialRepository = $this->createMock(ServiceTitanCredentialRepository::class);
        $this->syncLogRepository = $this->createMock(ServiceTitanSyncLogRepository::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);

        $this->service = new ServiceTitanMetricsService(
            $this->credentialRepository,
            $this->syncLogRepository,
            $this->cache
        );
    }

    public function testGetDashboardMetricsReturnsFromCache(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);

        $cachedData = [
            'credentials' => [],
            'alerts' => [],
            'syncSummary' => new ServiceTitanSyncSummaryDTO(0, 0, 0, 0, null, null, 0.0, []),
            'metrics' => new ServiceTitanMetricsDTO(0, 0, 0, 0, 0.0, 0.0, [], []),
            'lastUpdated' => new \DateTime(),
        ];

        $cacheItem->expects(self::once())
            ->method('get')
            ->willReturn($cachedData);

        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('servicetitan_dashboard_'.$company->getId())
            ->willReturn($cacheItem);

        // Act
        $result = $this->service->getDashboardMetrics($company);

        // Assert
        self::assertInstanceOf(ServiceTitanDashboardDTO::class, $result);
    }

    public function testGetDashboardMetricsGeneratesDataWhenNotCached(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::PRODUCTION);
        $credential->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);
        $credential->setUuid('test-uuid-123');

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->cache->expects(self::once())
            ->method('getItem')
            ->willReturn($cacheItem);

        $this->credentialRepository->expects(self::once())
            ->method('findByCompany')
            ->with($company)
            ->willReturn([$credential]);

        $this->syncLogRepository->expects(self::atLeastOnce())
            ->method('findByStatus')
            ->willReturn([]);

        $this->syncLogRepository->expects(self::atLeastOnce())
            ->method('getSyncStatistics')
            ->willReturn([
                'totalSyncs' => 10,
                'completedSyncs' => 8,
                'failedSyncs' => 2,
                'runningSyncs' => 0,
                'totalRecordsProcessed' => 100,
                'totalRecordsSuccessful' => 90,
                'totalRecordsFailed' => 10,
                'avgProcessingTime' => 30.5,
                'successRate' => 90.0,
            ]);

        $this->syncLogRepository->expects(self::atLeastOnce())
            ->method('findLastSuccessfulSync')
            ->willReturn(null);

        $this->syncLogRepository->expects(self::atLeastOnce())
            ->method('findByCredential')
            ->willReturn([]);

        $cacheItem->expects(self::once())
            ->method('set')
            ->with(self::isType('array'));

        $cacheItem->expects(self::once())
            ->method('expiresAfter')
            ->with(300);

        $this->cache->expects(self::once())
            ->method('save')
            ->with($cacheItem);

        // Act
        $result = $this->service->getDashboardMetrics($company);

        // Assert
        self::assertInstanceOf(ServiceTitanDashboardDTO::class, $result);
        self::assertIsArray($result->getCredentials());
        self::assertIsArray($result->getAlerts());
        self::assertNotNull($result->getSyncSummary());
        self::assertNotNull($result->getMetrics());
        self::assertInstanceOf(\DateTimeInterface::class, $result->getLastUpdated());
    }

    public function testClearDashboardCacheDeletesCorrectKey(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');

        $this->cache->expects(self::once())
            ->method('deleteItem')
            ->with('servicetitan_dashboard_'.$company->getId());

        // Act
        $this->service->clearDashboardCache($company);
    }

    public function testGenerateAlertsCreatesTokenExpiredAlert(): void
    {
        // This would require making generateAlerts public or testing through getDashboardMetrics
        // For now, we'll test through the main method
        $company = new Company();
        $company->setCompanyName('Test Company');

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::PRODUCTION);
        $credential->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);
        $credential->setUuid('test-uuid-123');
        $credential->setClientId('test-client-id');
        $credential->setClientSecret('test-client-secret');
        $credential->setTokenExpiresAt(new \DateTime('-1 hour')); // Expired

        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('isHit')->willReturn(false);

        $this->cache->method('getItem')->willReturn($cacheItem);
        $this->credentialRepository->method('findByCompany')->willReturn([$credential]);

        $this->syncLogRepository->method('findByStatus')->willReturn([]);
        $this->syncLogRepository->method('getSyncStatistics')->willReturn([
            'totalSyncs' => 0,
            'completedSyncs' => 0,
            'failedSyncs' => 0,
            'runningSyncs' => 0,
            'totalRecordsProcessed' => 0,
            'totalRecordsSuccessful' => 0,
            'totalRecordsFailed' => 0,
            'avgProcessingTime' => 0.0,
            'successRate' => 0.0,
        ]);
        $this->syncLogRepository->method('findLastSuccessfulSync')->willReturn(null);
        $this->syncLogRepository->method('findByCredential')->willReturn([]);

        $cacheItem->method('set')->willReturn($cacheItem);
        $cacheItem->method('expiresAfter')->willReturn($cacheItem);

        // Act
        $result = $this->service->getDashboardMetrics($company);

        // Assert
        $alerts = $result->getAlerts();
        self::assertNotEmpty($alerts);
        self::assertStringContainsString('Token Expired', $alerts[0]->getTitle());
    }
}

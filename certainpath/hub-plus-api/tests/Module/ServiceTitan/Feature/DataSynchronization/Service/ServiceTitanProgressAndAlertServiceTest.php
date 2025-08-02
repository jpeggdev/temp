<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Feature\DataSynchronization\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanAlertService;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanSyncProgressService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use App\Repository\CompanyRepository;
use App\Tests\AbstractKernelTestCase;

class ServiceTitanProgressAndAlertServiceTest extends AbstractKernelTestCase
{
    private ServiceTitanSyncProgressService $progressService;
    private ServiceTitanAlertService $alertService;
    private ServiceTitanCredentialRepository $credentialRepository;
    private ServiceTitanSyncLogRepository $syncLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanSyncProgressService $progressService */
        $progressService = $this->getService(ServiceTitanSyncProgressService::class);
        $this->progressService = $progressService;

        /** @var ServiceTitanAlertService $alertService */
        $alertService = $this->getService(ServiceTitanAlertService::class);
        $this->alertService = $alertService;

        /** @var ServiceTitanCredentialRepository $credentialRepo */
        $credentialRepo = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->credentialRepository = $credentialRepo;

        /** @var ServiceTitanSyncLogRepository $syncLogRepo */
        $syncLogRepo = $this->getRepository(ServiceTitanSyncLogRepository::class);
        $this->syncLogRepository = $syncLogRepo;
    }

    public function testProgressServiceReturnsNoActiveSync(): void
    {
        // Given
        $credential = $this->createTestCredential();

        // When
        $progress = $this->progressService->getActiveProgressForCredential($credential);

        // Then
        self::assertFalse($progress['hasActiveSync']);
        self::assertEmpty($progress['activeSyncs']);
    }

    public function testProgressServiceReturnsActiveSync(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = $this->createRunningSyncLog($credential);

        // When
        $progress = $this->progressService->getActiveProgressForCredential($credential);

        // Then
        self::assertTrue($progress['hasActiveSync']);
        self::assertCount(1, $progress['activeSyncs']);

        $activeSync = $progress['activeSyncs'][0];
        self::assertSame($syncLog->getId(), $activeSync['syncLogId']);
        self::assertSame($credential->getId(), $activeSync['credentialId']);
        self::assertSame('running', $activeSync['status']);
        self::assertArrayHasKey('progress', $activeSync);
        self::assertArrayHasKey('elapsedTime', $activeSync);
    }

    public function testProgressServiceUpdatesProgress(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = $this->createRunningSyncLog($credential);

        // When
        $this->progressService->updateSyncProgress($syncLog, 50, 45, 5, 'Processing customers');

        // Then
        $this->getEntityManager()->refresh($syncLog);
        self::assertSame(50, $syncLog->getRecordsProcessed());
        self::assertSame(45, $syncLog->getRecordsSuccessful());
        self::assertSame(5, $syncLog->getRecordsFailed());
        self::assertSame(90.0, $syncLog->getSuccessRate());
    }

    public function testGlobalSyncProgressReturnsActiveSyncs(): void
    {
        // Given
        $credential1 = $this->createTestCredential();
        $credential2 = $this->createTestCredential();

        $syncLog1 = $this->createRunningSyncLog($credential1);
        $syncLog2 = $this->createRunningSyncLog($credential2);

        // When
        $globalProgress = $this->progressService->getGlobalSyncProgress();

        // Then
        self::assertTrue($globalProgress['hasActiveSyncs']);
        self::assertSame(2, $globalProgress['totalActiveSyncs']);
        self::assertCount(2, $globalProgress['syncs']);
    }

    public function testAlertServiceDetectsFailedSync(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = $this->createRunningSyncLog($credential);
        $syncLog->markAsFailed('Test failure');
        $this->syncLogRepository->save($syncLog, true);

        // When
        $alerts = $this->alertService->checkForSyncFailures();

        // Then
        self::assertNotEmpty($alerts);
        $failureAlert = array_filter($alerts, fn ($alert) => $alert['type'] === 'sync_failure');
        self::assertNotEmpty($failureAlert);

        $alert = array_values($failureAlert)[0];
        self::assertSame('high', $alert['severity']);
        self::assertSame($syncLog->getId(), $alert['syncLogId']);
        self::assertStringContainsString('failed', $alert['message']);
    }

    public function testAlertServiceDetectsLongRunningSync(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = $this->createRunningSyncLog($credential);

        // Simulate a sync that started 2 hours ago
        $twoHoursAgo = new \DateTime('-2 hours');
        $syncLog->setStartedAt($twoHoursAgo);
        $this->syncLogRepository->save($syncLog, true);

        // When
        $alerts = $this->alertService->checkForSyncFailures();

        // Then
        self::assertNotEmpty($alerts);
        $longRunningAlert = array_filter($alerts, fn ($alert) => $alert['type'] === 'long_running_sync');
        self::assertNotEmpty($longRunningAlert);

        $alert = array_values($longRunningAlert)[0];
        self::assertSame('medium', $alert['severity']);
        self::assertSame($syncLog->getId(), $alert['syncLogId']);
        self::assertStringContainsString('running for', $alert['message']);
    }

    public function testSystemHealthDashboard(): void
    {
        // Given - Create some test syncs
        $credential = $this->createTestCredential();
        $this->createRunningSyncLog($credential);

        // When
        $dashboard = $this->alertService->getSystemHealthDashboard();

        // Then
        self::assertArrayHasKey('last24Hours', $dashboard);
        self::assertArrayHasKey('activeSyncs', $dashboard);
        self::assertArrayHasKey('systemStatus', $dashboard);
        self::assertArrayHasKey('alerts', $dashboard);
        self::assertArrayHasKey('updatedAt', $dashboard);

        // Verify structure of last24Hours metrics
        $metrics = $dashboard['last24Hours'];
        self::assertArrayHasKey('totalSyncs', $metrics);
        self::assertArrayHasKey('successfulSyncs', $metrics);
        self::assertArrayHasKey('failedSyncs', $metrics);
        self::assertArrayHasKey('successRate', $metrics);
        self::assertArrayHasKey('avgProcessingTime', $metrics);
    }

    private function createTestCredential(): ServiceTitanCredential
    {
        $company = $this->createTestCompany();

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company)
            ->setClientId('test_client_id_'.uniqid('', true))
            ->setClientSecret('test_client_secret')
            ->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setAccessToken('test_access_token')
            ->setRefreshToken('test_refresh_token')
            ->setTokenExpiresAt(new \DateTime('+1 hour'));

        $this->credentialRepository->save($credential, true);

        return $credential;
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName('Test Progress Company '.uniqid('', true))
            ->setUuid('test-progress-uuid-'.uniqid('', true));

        /** @var CompanyRepository $companyRepo */
        $companyRepo = $this->getRepository(CompanyRepository::class);
        $companyRepository = $companyRepo;
        $companyRepository->save($company, true);

        return $company;
    }

    private function createRunningSyncLog(ServiceTitanCredential $credential): ServiceTitanSyncLog
    {
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::MANUAL)
            ->setDataType(ServiceTitanSyncDataType::INVOICES);

        $this->syncLogRepository->save($syncLog, true);

        return $syncLog;
    }
}

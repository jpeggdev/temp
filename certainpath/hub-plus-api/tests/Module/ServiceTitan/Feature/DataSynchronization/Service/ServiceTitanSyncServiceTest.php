<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Feature\DataSynchronization\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanSyncService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use App\Repository\CompanyRepository;
use App\Tests\AbstractKernelTestCase;

class ServiceTitanSyncServiceTest extends AbstractKernelTestCase
{
    private ServiceTitanSyncService $syncService;
    private ServiceTitanCredentialRepository $credentialRepository;
    private ServiceTitanSyncLogRepository $syncLogRepository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanSyncService $syncService */
        $syncService = $this->getService(ServiceTitanSyncService::class);
        $this->syncService = $syncService;

        /** @var ServiceTitanCredentialRepository $credentialRepo */
        $credentialRepo = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->credentialRepository = $credentialRepo;

        /** @var ServiceTitanSyncLogRepository $syncLogRepo */
        $syncLogRepo = $this->getRepository(ServiceTitanSyncLogRepository::class);
        $this->syncLogRepository = $syncLogRepo;

    }

    public function testCanTriggerManualInvoiceSync(): void
    {
        // Given
        $credential = $this->createTestCredentialWithActiveConnection();

        // When
        $syncLog = $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::INVOICES,
            ServiceTitanSyncType::MANUAL
        );

        // Then
        self::assertNotNull($syncLog);
        self::assertSame($credential, $syncLog->getServiceTitanCredential());
        self::assertSame(ServiceTitanSyncType::MANUAL, $syncLog->getSyncType());
        self::assertSame(ServiceTitanSyncDataType::INVOICES, $syncLog->getDataType());
        self::assertSame(ServiceTitanSyncStatus::RUNNING, $syncLog->getStatus());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getStartedAt());
    }

    public function testCanTriggerManualCustomerSync(): void
    {
        // Given
        $credential = $this->createTestCredentialWithActiveConnection();

        // When
        $syncLog = $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::CUSTOMERS,
            ServiceTitanSyncType::MANUAL
        );

        // Then
        self::assertSame(ServiceTitanSyncDataType::CUSTOMERS, $syncLog->getDataType());
    }

    public function testCanTriggerBothDataTypesSync(): void
    {
        // Given
        $credential = $this->createTestCredentialWithActiveConnection();

        // When
        $syncLog = $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::BOTH,
            ServiceTitanSyncType::MANUAL
        );

        // Then
        self::assertSame(ServiceTitanSyncDataType::BOTH, $syncLog->getDataType());
    }

    public function testCannotTriggerSyncWithInactiveCredential(): void
    {
        // Given
        $credential = $this->createTestCredentialWithInactiveConnection();

        // When & Then
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ServiceTitan credential is not active');

        $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::INVOICES,
            ServiceTitanSyncType::MANUAL
        );
    }

    public function testCannotTriggerSyncWhenAnotherSyncIsRunning(): void
    {
        // Given
        $credential = $this->createTestCredentialWithActiveConnection();
        $this->createRunningSyncLog($credential);

        // When & Then
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('A sync is already running for this credential');

        $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::INVOICES,
            ServiceTitanSyncType::MANUAL
        );
    }

    public function testCanGetSyncStatus(): void
    {
        // Given
        $credential = $this->createTestCredentialWithActiveConnection();
        $syncLog = $this->createCompletedSyncLog($credential);

        // When
        $status = $this->syncService->getSyncStatus($credential);

        // Then
        self::assertNotNull($status);
        self::assertArrayHasKey('currentSync', $status);
        self::assertArrayHasKey('lastCompletedSync', $status);
        self::assertNull($status['currentSync']); // No running sync
        self::assertNotNull($status['lastCompletedSync']);
        self::assertSame($syncLog->getId(), $status['lastCompletedSync']['id']);
    }

    public function testCanGetSyncStatusWithRunningSync(): void
    {
        // Given
        $credential = $this->createTestCredentialWithActiveConnection();
        $runningSyncLog = $this->createRunningSyncLog($credential);

        // When
        $status = $this->syncService->getSyncStatus($credential);

        // Then
        self::assertNotNull($status['currentSync']);
        self::assertSame($runningSyncLog->getId(), $status['currentSync']['id']);
        self::assertSame('running', $status['currentSync']['status']);
    }

    private function createTestCredentialWithActiveConnection(): ServiceTitanCredential
    {
        $company = $this->createTestCompany();

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company)
            ->setClientId('test_client_id')
            ->setClientSecret('test_client_secret')
            ->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setAccessToken('test_access_token')
            ->setRefreshToken('test_refresh_token')
            ->setTokenExpiresAt(new \DateTime('+1 hour'));

        $this->credentialRepository->save($credential, true);

        return $credential;
    }

    private function createTestCredentialWithInactiveConnection(): ServiceTitanCredential
    {
        $company = $this->createTestCompany();

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company)
            ->setClientId('test_client_id')
            ->setClientSecret('test_client_secret')
            ->setConnectionStatus(ServiceTitanConnectionStatus::INACTIVE)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);

        $this->credentialRepository->save($credential, true);

        return $credential;
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName('Test ServiceTitan Company')
            ->setUuid('test-st-uuid-'.uniqid('', true));

        $this->companyRepository->save($company, true);

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

    private function createCompletedSyncLog(ServiceTitanCredential $credential): ServiceTitanSyncLog
    {
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::MANUAL)
            ->setDataType(ServiceTitanSyncDataType::INVOICES)
            ->setRecordsProcessed(100)
            ->setRecordsSuccessful(95)
            ->setRecordsFailed(5);

        $syncLog->markAsCompleted();

        $this->syncLogRepository->save($syncLog, true);

        return $syncLog;
    }
}

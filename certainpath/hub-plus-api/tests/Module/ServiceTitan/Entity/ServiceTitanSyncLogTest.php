<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Entity;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use App\Repository\CompanyRepository;
use App\Tests\AbstractKernelTestCase;

class ServiceTitanSyncLogTest extends AbstractKernelTestCase
{
    private ServiceTitanSyncLogRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanSyncLogRepository $repo */
        $repo = $this->getRepository(ServiceTitanSyncLogRepository::class);
        $this->repository = $repo;
    }

    public function testCanCreateServiceTitanSyncLog(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = new ServiceTitanSyncLog();

        // When
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::MANUAL)
            ->setDataType(ServiceTitanSyncDataType::INVOICES);

        $this->repository->save($syncLog, true);

        // Then
        self::assertNotNull($syncLog->getId());
        self::assertSame($credential, $syncLog->getServiceTitanCredential());
        self::assertSame(ServiceTitanSyncType::MANUAL, $syncLog->getSyncType());
        self::assertSame(ServiceTitanSyncDataType::INVOICES, $syncLog->getDataType());
        self::assertSame(ServiceTitanSyncStatus::RUNNING, $syncLog->getStatus());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getStartedAt());
        self::assertNull($syncLog->getCompletedAt());
        self::assertSame(0, $syncLog->getRecordsProcessed());
        self::assertSame(0, $syncLog->getRecordsSuccessful());
        self::assertSame(0, $syncLog->getRecordsFailed());
        self::assertNull($syncLog->getErrorMessage());
        self::assertNull($syncLog->getErrorDetails());
        self::assertNull($syncLog->getProcessingTimeSeconds());
    }

    public function testConstructorSetsDefaults(): void
    {
        // When
        $syncLog = new ServiceTitanSyncLog();

        // Then
        self::assertSame(ServiceTitanSyncStatus::RUNNING, $syncLog->getStatus());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getStartedAt());
        self::assertSame(0, $syncLog->getRecordsProcessed());
        self::assertSame(0, $syncLog->getRecordsSuccessful());
        self::assertSame(0, $syncLog->getRecordsFailed());
    }

    public function testCanSetAllEnumTypes(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();

        // When & Then - Sync Types
        $syncLog->setSyncType(ServiceTitanSyncType::MANUAL);
        self::assertSame(ServiceTitanSyncType::MANUAL, $syncLog->getSyncType());

        $syncLog->setSyncType(ServiceTitanSyncType::SCHEDULED);
        self::assertSame(ServiceTitanSyncType::SCHEDULED, $syncLog->getSyncType());

        // Data Types
        $syncLog->setDataType(ServiceTitanSyncDataType::INVOICES);
        self::assertSame(ServiceTitanSyncDataType::INVOICES, $syncLog->getDataType());

        $syncLog->setDataType(ServiceTitanSyncDataType::CUSTOMERS);
        self::assertSame(ServiceTitanSyncDataType::CUSTOMERS, $syncLog->getDataType());

        $syncLog->setDataType(ServiceTitanSyncDataType::BOTH);
        self::assertSame(ServiceTitanSyncDataType::BOTH, $syncLog->getDataType());

        // Status Types
        $syncLog->setStatus(ServiceTitanSyncStatus::RUNNING);
        self::assertSame(ServiceTitanSyncStatus::RUNNING, $syncLog->getStatus());

        $syncLog->setStatus(ServiceTitanSyncStatus::COMPLETED);
        self::assertSame(ServiceTitanSyncStatus::COMPLETED, $syncLog->getStatus());

        $syncLog->setStatus(ServiceTitanSyncStatus::FAILED);
        self::assertSame(ServiceTitanSyncStatus::FAILED, $syncLog->getStatus());
    }

    public function testCanSetAllProperties(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = new ServiceTitanSyncLog();
        $startedAt = new \DateTime('2024-01-01 10:00:00');
        $completedAt = new \DateTime('2024-01-01 10:05:00');
        $errorDetails = ['error' => 'API timeout', 'code' => 500];

        // When
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::SCHEDULED)
            ->setDataType(ServiceTitanSyncDataType::BOTH)
            ->setStatus(ServiceTitanSyncStatus::FAILED)
            ->setStartedAt($startedAt)
            ->setCompletedAt($completedAt)
            ->setRecordsProcessed(100)
            ->setRecordsSuccessful(80)
            ->setRecordsFailed(20)
            ->setErrorMessage('Connection timeout')
            ->setErrorDetails($errorDetails)
            ->setProcessingTimeSeconds(300);

        // Then
        self::assertSame($credential, $syncLog->getServiceTitanCredential());
        self::assertSame(ServiceTitanSyncType::SCHEDULED, $syncLog->getSyncType());
        self::assertSame(ServiceTitanSyncDataType::BOTH, $syncLog->getDataType());
        self::assertSame(ServiceTitanSyncStatus::FAILED, $syncLog->getStatus());
        self::assertSame($startedAt, $syncLog->getStartedAt());
        self::assertSame($completedAt, $syncLog->getCompletedAt());
        self::assertSame(100, $syncLog->getRecordsProcessed());
        self::assertSame(80, $syncLog->getRecordsSuccessful());
        self::assertSame(20, $syncLog->getRecordsFailed());
        self::assertSame('Connection timeout', $syncLog->getErrorMessage());
        self::assertSame($errorDetails, $syncLog->getErrorDetails());
        self::assertSame(300, $syncLog->getProcessingTimeSeconds());
    }

    public function testMarkAsCompleted(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $startTime = new \DateTime('-5 minutes');
        $syncLog->setStartedAt($startTime);

        // When
        $syncLog->markAsCompleted();

        // Then
        self::assertSame(ServiceTitanSyncStatus::COMPLETED, $syncLog->getStatus());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getCompletedAt());
        self::assertNotNull($syncLog->getProcessingTimeSeconds());
        self::assertGreaterThan(0, $syncLog->getProcessingTimeSeconds());
    }

    public function testMarkAsFailedWithoutErrorDetails(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $startTime = new \DateTime('-3 minutes');
        $syncLog->setStartedAt($startTime);

        // When
        $syncLog->markAsFailed();

        // Then
        self::assertSame(ServiceTitanSyncStatus::FAILED, $syncLog->getStatus());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getCompletedAt());
        self::assertNotNull($syncLog->getProcessingTimeSeconds());
        self::assertGreaterThan(0, $syncLog->getProcessingTimeSeconds());
        self::assertNull($syncLog->getErrorMessage());
        self::assertNull($syncLog->getErrorDetails());
    }

    public function testMarkAsFailedWithErrorDetails(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $startTime = new \DateTime('-2 minutes');
        $syncLog->setStartedAt($startTime);
        $errorMessage = 'API request failed';
        $errorDetails = ['status_code' => 400, 'response' => 'Bad Request'];

        // When
        $syncLog->markAsFailed($errorMessage, $errorDetails);

        // Then
        self::assertSame(ServiceTitanSyncStatus::FAILED, $syncLog->getStatus());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getCompletedAt());
        self::assertNotNull($syncLog->getProcessingTimeSeconds());
        self::assertSame($errorMessage, $syncLog->getErrorMessage());
        self::assertSame($errorDetails, $syncLog->getErrorDetails());
    }

    public function testUpdateRecordCounts(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();

        // When - First update
        $syncLog->updateRecordCounts(50, 45, 5);

        // Then
        self::assertSame(50, $syncLog->getRecordsProcessed());
        self::assertSame(45, $syncLog->getRecordsSuccessful());
        self::assertSame(5, $syncLog->getRecordsFailed());

        // When - Second update (should accumulate)
        $syncLog->updateRecordCounts(30, 25, 5);

        // Then
        self::assertSame(80, $syncLog->getRecordsProcessed());
        self::assertSame(70, $syncLog->getRecordsSuccessful());
        self::assertSame(10, $syncLog->getRecordsFailed());
    }

    public function testStatusCheckMethods(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();

        // When & Then - RUNNING status
        $syncLog->setStatus(ServiceTitanSyncStatus::RUNNING);
        self::assertTrue($syncLog->isRunning());
        self::assertFalse($syncLog->wasSuccessful());
        self::assertFalse($syncLog->hasFailed());

        // When & Then - COMPLETED status
        $syncLog->setStatus(ServiceTitanSyncStatus::COMPLETED);
        self::assertFalse($syncLog->isRunning());
        self::assertTrue($syncLog->wasSuccessful());
        self::assertFalse($syncLog->hasFailed());

        // When & Then - FAILED status
        $syncLog->setStatus(ServiceTitanSyncStatus::FAILED);
        self::assertFalse($syncLog->isRunning());
        self::assertFalse($syncLog->wasSuccessful());
        self::assertTrue($syncLog->hasFailed());
    }

    public function testGetSuccessRateWithNoRecords(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();

        // When & Then
        self::assertSame(0.0, $syncLog->getSuccessRate());
    }

    public function testGetSuccessRateWithRecords(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setRecordsProcessed(100)
            ->setRecordsSuccessful(85)
            ->setRecordsFailed(15);

        // When & Then
        self::assertSame(85.0, $syncLog->getSuccessRate());
    }

    public function testGetSuccessRateWithPartialSuccess(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setRecordsProcessed(3)
            ->setRecordsSuccessful(2)
            ->setRecordsFailed(1);

        // When & Then
        self::assertEqualsWithDelta(66.67, $syncLog->getSuccessRate(), 0.01);
    }

    public function testGetDurationStringWithNoProcessingTime(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();

        // When & Then
        self::assertNull($syncLog->getDurationString());
    }

    public function testGetDurationStringWithSeconds(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setProcessingTimeSeconds(45);

        // When & Then
        self::assertSame('45s', $syncLog->getDurationString());
    }

    public function testGetDurationStringWithMinutes(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setProcessingTimeSeconds(125); // 2m 5s

        // When & Then
        self::assertSame('2m 5s', $syncLog->getDurationString());
    }

    public function testGetDurationStringWithHours(): void
    {
        // Given
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setProcessingTimeSeconds(7325); // 2h 2m 5s

        // When & Then
        self::assertSame('2h 2m 5s', $syncLog->getDurationString());
    }

    public function testCanPersistAndRetrieveWithJsonField(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = new ServiceTitanSyncLog();
        $errorDetails = [
            'api_error' => 'Rate limit exceeded',
            'retry_after' => 300,
            'request_id' => 'req_123456',
            'nested' => ['key' => 'value', 'count' => 42]
        ];

        // When
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::MANUAL)
            ->setDataType(ServiceTitanSyncDataType::CUSTOMERS)
            ->setErrorDetails($errorDetails);

        $this->repository->save($syncLog, true);

        // Clear entity manager to force database retrieval
        $this->getEntityManager()->clear();

        // Then
        $retrievedLog = $this->repository->find($syncLog->getId());
        self::assertNotNull($retrievedLog);
        self::assertSame($errorDetails, $retrievedLog->getErrorDetails());
    }

    public function testCascadeDeleteWhenCredentialIsDeleted(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::MANUAL)
            ->setDataType(ServiceTitanSyncDataType::INVOICES);

        $this->repository->save($syncLog, true);
        $syncLogId = $syncLog->getId();

        // When - Delete the credential
        $credentialRepository = $this->getRepository(\App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository::class);
        $credentialRepository->remove($credential, true);

        // Then - Sync log should be deleted too
        $this->getEntityManager()->clear();
        $deletedSyncLog = $this->repository->find($syncLogId);
        self::assertNull($deletedSyncLog);
    }

    public function testTimestampableTraitsWork(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = new ServiceTitanSyncLog();

        // When
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::SCHEDULED)
            ->setDataType(ServiceTitanSyncDataType::BOTH);

        $this->repository->save($syncLog, true);

        // Then
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getCreatedAt());
        self::assertInstanceOf(\DateTimeInterface::class, $syncLog->getUpdatedAt());
    }

    public function testUuidTraitWorks(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog = new ServiceTitanSyncLog();

        // When
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType(ServiceTitanSyncType::MANUAL)
            ->setDataType(ServiceTitanSyncDataType::INVOICES);

        $this->repository->save($syncLog, true);

        // Then
        self::assertNotNull($syncLog->getUuid());
        self::assertIsString($syncLog->getUuid());
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $syncLog->getUuid());
    }

    private function createTestCredential(): ServiceTitanCredential
    {
        $company = $this->createTestCompany();

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setClientId('test-client-id')
            ->setClientSecret('test-client-secret')
            ->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);

        $credentialRepository = $this->getRepository(\App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository::class);
        $credentialRepository->save($credential, true);

        return $credential;
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName('Test Company '.uniqid('', true))
            ->setIntacctId(uniqid('intacct_', true));

        /** @var CompanyRepository $companyRepo */
        $companyRepo = $this->getRepository(CompanyRepository::class);
        $companyRepo->save($company, true);

        return $company;
    }
}

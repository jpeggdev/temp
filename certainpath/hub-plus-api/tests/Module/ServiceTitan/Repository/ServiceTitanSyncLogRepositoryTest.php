<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Repository;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use App\Repository\CompanyRepository;
use App\Tests\AbstractKernelTestCase;

class ServiceTitanSyncLogRepositoryTest extends AbstractKernelTestCase
{
    private ServiceTitanSyncLogRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanSyncLogRepository $repo */
        $repo = $this->getRepository(ServiceTitanSyncLogRepository::class);
        $this->repository = $repo;
    }

    public function testFindByCredential(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $syncLog1 = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);
        $syncLog2 = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);

        // Other credential logs that should not be returned
        $otherCredential = $this->createTestCredential();
        $this->createSyncLog($otherCredential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::BOTH);

        // When
        $results = $this->repository->findByCredential($credential);

        // Then
        self::assertCount(2, $results);
        $resultIds = array_map(fn (ServiceTitanSyncLog $log) => $log->getId(), $results);
        self::assertContains($syncLog1->getId(), $resultIds);
        self::assertContains($syncLog2->getId(), $resultIds);
    }

    public function testFindByStatus(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $runningLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);

        $completedLog = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);
        $completedLog->markAsCompleted();
        $this->repository->save($completedLog, true);

        $failedLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::BOTH);
        $failedLog->markAsFailed('Test error');
        $this->repository->save($failedLog, true);

        // When
        $runningResults = $this->repository->findByStatus(ServiceTitanSyncStatus::RUNNING);
        $completedResults = $this->repository->findByStatus(ServiceTitanSyncStatus::COMPLETED);
        $failedResults = $this->repository->findByStatus(ServiceTitanSyncStatus::FAILED);

        // Then
        self::assertCount(1, $runningResults);
        self::assertSame($runningLog->getId(), $runningResults[0]->getId());

        self::assertCount(1, $completedResults);
        self::assertSame($completedLog->getId(), $completedResults[0]->getId());

        self::assertCount(1, $failedResults);
        self::assertSame($failedLog->getId(), $failedResults[0]->getId());
    }

    public function testFindRunningByCredential(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $runningLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);

        $completedLog = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);
        $completedLog->markAsCompleted();
        $this->repository->save($completedLog, true);

        // When
        $results = $this->repository->findRunningByCredential($credential);

        // Then
        self::assertCount(1, $results);
        self::assertSame($runningLog->getId(), $results[0]->getId());
        self::assertTrue($results[0]->isRunning());
    }

    public function testFindBySyncAndDataType(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $matchingLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);
        $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::INVOICES);
        $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::CUSTOMERS);

        // When
        $results = $this->repository->findBySyncAndDataType(
            ServiceTitanSyncType::MANUAL,
            ServiceTitanSyncDataType::INVOICES
        );

        // Then
        self::assertCount(1, $results);
        self::assertSame($matchingLog->getId(), $results[0]->getId());
    }

    public function testFindRecentLogs(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $recentLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);

        $oldLog = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);
        $oldLog->setStartedAt(new \DateTime('-2 days'));
        $this->repository->save($oldLog, true);

        // When
        $results = $this->repository->findRecentLogs(new \DateTime('-1 day'));

        // Then
        self::assertCount(1, $results);
        self::assertSame($recentLog->getId(), $results[0]->getId());
    }

    public function testFindFailedLogs(): void
    {
        // Given
        $credential = $this->createTestCredential();
        $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES); // running

        $failedLog = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);
        $failedLog->markAsFailed('Test error');
        $this->repository->save($failedLog, true);

        // When
        $results = $this->repository->findFailedLogs();

        // Then
        self::assertCount(1, $results);
        self::assertSame($failedLog->getId(), $results[0]->getId());
        self::assertTrue($results[0]->hasFailed());
    }

    public function testGetSyncStatistics(): void
    {
        // Given
        $credential = $this->createTestCredential();

        // Create completed sync
        $completedLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);
        $completedLog->updateRecordCounts(100, 90, 10);
        $completedLog->markAsCompleted();
        $this->repository->save($completedLog, true);

        // Create failed sync
        $failedLog = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);
        $failedLog->updateRecordCounts(50, 30, 20);
        $failedLog->markAsFailed('Test error');
        $this->repository->save($failedLog, true);

        // Create running sync
        $runningLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::BOTH);
        $runningLog->updateRecordCounts(25, 20, 5);
        $this->repository->save($runningLog, true);

        // When
        $stats = $this->repository->getSyncStatistics(
            $credential,
            new \DateTime('-1 day'),
            new \DateTime('+1 day')
        );

        // Then
        self::assertSame(3, $stats['totalSyncs']);
        self::assertSame(1, $stats['completedSyncs']);
        self::assertSame(1, $stats['failedSyncs']);
        self::assertSame(1, $stats['runningSyncs']);
        self::assertSame(175, $stats['totalRecordsProcessed']);
        self::assertSame(140, $stats['totalRecordsSuccessful']);
        self::assertSame(35, $stats['totalRecordsFailed']);
        self::assertEqualsWithDelta(80.0, $stats['successRate'], 0.1);
    }

    public function testFindLastSuccessfulSync(): void
    {
        // Given
        $credential = $this->createTestCredential();

        // Create older successful sync
        $olderSync = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);
        $olderSync->setStartedAt(new \DateTime('-2 hours'));
        $olderSync->markAsCompleted();
        // Manually set older completion time after marking as completed
        $olderSync->setCompletedAt(new \DateTime('-2 hours'));
        $this->repository->save($olderSync, true);

        // Create newer successful sync
        $newerSync = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::INVOICES);
        $newerSync->setStartedAt(new \DateTime('-1 hour'));
        $newerSync->markAsCompleted();
        // Manually set newer completion time after marking as completed
        $newerSync->setCompletedAt(new \DateTime('-1 hour'));
        $this->repository->save($newerSync, true);

        // Create failed sync (should be ignored)
        $failedSync = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);
        $failedSync->markAsFailed('Test error');
        $this->repository->save($failedSync, true);

        // When
        $result = $this->repository->findLastSuccessfulSync($credential, ServiceTitanSyncDataType::INVOICES);

        // Then
        self::assertNotNull($result);
        self::assertSame($newerSync->getId(), $result->getId());
        self::assertTrue($result->wasSuccessful());
    }

    public function testHasActiveSyncForCredential(): void
    {
        // Given
        $credential1 = $this->createTestCredential();
        $credential2 = $this->createTestCredential();

        // Create running sync for credential1
        $this->createSyncLog($credential1, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);

        // Create completed sync for credential2
        $completedSync = $this->createSyncLog($credential2, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::CUSTOMERS);
        $completedSync->markAsCompleted();
        $this->repository->save($completedSync, true);

        // When/Then
        self::assertTrue($this->repository->hasActiveSyncForCredential($credential1));
        self::assertFalse($this->repository->hasActiveSyncForCredential($credential2));
    }

    public function testDeleteLogsOlderThan(): void
    {
        // Given
        $credential = $this->createTestCredential();

        // Create old log
        $oldLog = $this->createSyncLog($credential, ServiceTitanSyncType::MANUAL, ServiceTitanSyncDataType::INVOICES);
        $oldLog->setStartedAt(new \DateTime('-2 days'));
        $this->repository->save($oldLog, true);

        // Create recent log
        $recentLog = $this->createSyncLog($credential, ServiceTitanSyncType::SCHEDULED, ServiceTitanSyncDataType::CUSTOMERS);
        $this->repository->save($recentLog, true);

        // When
        $deletedCount = $this->repository->deleteLogsOlderThan(new \DateTime('-1 day'));

        // Then
        self::assertSame(1, $deletedCount);

        // Verify the old log is gone and recent log remains
        $this->getEntityManager()->clear();
        self::assertNull($this->repository->find($oldLog->getId()));
        self::assertNotNull($this->repository->find($recentLog->getId()));
    }

    private function createSyncLog(
        ServiceTitanCredential $credential,
        ServiceTitanSyncType $syncType,
        ServiceTitanSyncDataType $dataType
    ): ServiceTitanSyncLog {
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType($syncType)
            ->setDataType($dataType);

        $this->repository->save($syncLog, true);

        return $syncLog;
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

        $credentialRepository = $this->getRepository(ServiceTitanCredentialRepository::class);
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

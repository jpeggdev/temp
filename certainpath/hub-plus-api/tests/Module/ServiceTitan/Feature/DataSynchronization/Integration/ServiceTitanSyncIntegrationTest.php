<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Feature\DataSynchronization\Integration;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanSyncService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Repository\CompanyRepository;
use App\Tests\AbstractKernelTestCase;

class ServiceTitanSyncIntegrationTest extends AbstractKernelTestCase
{
    private ServiceTitanSyncService $syncService;
    private ServiceTitanCredentialRepository $credentialRepository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanSyncService $syncService */
        $syncService = $this->getService(ServiceTitanSyncService::class);
        $this->syncService = $syncService;

        /** @var ServiceTitanCredentialRepository $credentialRepo */
        $credentialRepo = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->credentialRepository = $credentialRepo;

    }

    public function testFullSyncWorkflow(): void
    {
        // Given - Create a test credential
        $credential = $this->createTestCredential();

        // When - Trigger a sync
        $syncLog = $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::INVOICES,
            ServiceTitanSyncType::MANUAL
        );

        // Then - Verify sync was created
        self::assertNotNull($syncLog);
        self::assertSame(ServiceTitanSyncDataType::INVOICES, $syncLog->getDataType());
        self::assertSame(ServiceTitanSyncType::MANUAL, $syncLog->getSyncType());

        // When - Check sync status
        $status = $this->syncService->getSyncStatus($credential);

        // Then - Verify status shows running sync
        self::assertNotNull($status['currentSync']);
        self::assertSame($syncLog->getId(), $status['currentSync']['id']);
        self::assertSame('running', $status['currentSync']['status']);

        // When - Check sync history
        $history = $this->syncService->getSyncHistory($credential, 1, 10);

        // Then - Verify history contains the sync
        self::assertNotEmpty($history['items']);
        self::assertSame($syncLog->getId(), $history['items'][0]['id']);
        self::assertArrayHasKey('pagination', $history);
        self::assertSame(1, $history['pagination']['page']);
        self::assertSame(10, $history['pagination']['limit']);
    }

    public function testSyncHistoryPagination(): void
    {
        // Given - Create credential with one sync
        $credential = $this->createTestCredential();

        $sync1 = $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::INVOICES,
            ServiceTitanSyncType::MANUAL
        );

        // When - Check history with small limit
        $history = $this->syncService->getSyncHistory($credential, 1, 5);

        // Then - Verify pagination structure
        self::assertArrayHasKey('items', $history);
        self::assertArrayHasKey('pagination', $history);
        self::assertSame(1, $history['pagination']['page']);
        self::assertSame(5, $history['pagination']['limit']);
        self::assertNotEmpty($history['items']);
    }

    public function testSyncCancellation(): void
    {
        // Given - Create credential and start a sync
        $credential = $this->createTestCredential();
        $syncLog = $this->syncService->triggerSync(
            $credential,
            ServiceTitanSyncDataType::BOTH,
            ServiceTitanSyncType::MANUAL
        );

        // When - Cancel the sync
        $result = $this->syncService->cancelSync($credential);

        // Then - Verify cancellation was successful
        self::assertTrue($result);

        // And - Verify sync is marked as failed
        $this->getEntityManager()->refresh($syncLog);
        self::assertTrue($syncLog->hasFailed());
        self::assertStringContainsString('cancelled', strtolower($syncLog->getErrorMessage() ?? ''));
    }

    private function createTestCredential(): ServiceTitanCredential
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

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName('Test ServiceTitan Integration Company')
            ->setUuid('test-st-integration-uuid-'.uniqid('', true));

        /** @var CompanyRepository $companyRepo */
        $companyRepo = $this->getRepository(CompanyRepository::class);
        $companyRepository = $companyRepo;
        $companyRepository->save($company, true);

        return $company;
    }
}

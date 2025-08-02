<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Entity;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Repository\CompanyRepository;
use App\Tests\AbstractKernelTestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class ServiceTitanCredentialTest extends AbstractKernelTestCase
{
    private ServiceTitanCredentialRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanCredentialRepository $repo */
        $repo = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->repository = $repo;
    }

    public function testCanCreateServiceTitanCredential(): void
    {
        // Given
        $company = $this->createTestCompany();
        $credential = new ServiceTitanCredential();

        // When
        $credential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setClientId('test-client-id')
            ->setClientSecret('test-client-secret')
            ->setAccessToken('test-access-token')
            ->setRefreshToken('test-refresh-token')
            ->setTokenExpiresAt(new \DateTime('+1 hour'))
            ->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);

        $this->repository->save($credential, true);

        // Then
        self::assertNotNull($credential->getId());
        self::assertSame($company, $credential->getCompany());
        self::assertSame(ServiceTitanEnvironment::INTEGRATION, $credential->getEnvironment());
        self::assertSame('test-client-id', $credential->getClientId());
        self::assertSame('test-client-secret', $credential->getClientSecret());
        self::assertSame('test-access-token', $credential->getAccessToken());
        self::assertSame('test-refresh-token', $credential->getRefreshToken());
        self::assertInstanceOf(\DateTimeInterface::class, $credential->getTokenExpiresAt());
        self::assertSame(ServiceTitanConnectionStatus::ACTIVE, $credential->getConnectionStatus());
        self::assertNotNull($credential->getUuid());
        self::assertNotNull($credential->getCreatedAt());
        self::assertNotNull($credential->getUpdatedAt());
    }

    public function testDefaultConnectionStatusIsInactive(): void
    {
        // Given
        $company = $this->createTestCompany();
        $credential = new ServiceTitanCredential();

        // When
        $credential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::PRODUCTION);

        // Then
        self::assertSame(ServiceTitanConnectionStatus::INACTIVE, $credential->getConnectionStatus());
    }

    public function testUniqueConstraintOnCompanyAndEnvironment(): void
    {
        // Given
        $company = $this->createTestCompany();

        $credential1 = new ServiceTitanCredential();
        $credential1->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $this->repository->save($credential1, true);

        $credential2 = new ServiceTitanCredential();
        $credential2->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);

        // When/Then
        $this->expectException(UniqueConstraintViolationException::class);
        $this->repository->save($credential2, true);
    }

    public function testCanHaveMultipleEnvironmentsForSameCompany(): void
    {
        // Given
        $company = $this->createTestCompany();

        $integrationCredential = new ServiceTitanCredential();
        $integrationCredential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $this->repository->save($integrationCredential, true);

        $productionCredential = new ServiceTitanCredential();
        $productionCredential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::PRODUCTION);

        // When/Then - should not throw exception
        $this->repository->save($productionCredential, true);

        self::assertNotNull($integrationCredential->getId());
        self::assertNotNull($productionCredential->getId());
        self::assertNotSame($integrationCredential->getId(), $productionCredential->getId());
    }

    public function testIsTokenExpiredWithNullExpirationReturnsTrue(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setTokenExpiresAt(null);

        // When/Then
        self::assertTrue($credential->isTokenExpired());
    }

    public function testIsTokenExpiredWithFutureExpirationReturnsFalse(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setTokenExpiresAt(new \DateTime('+1 hour'));

        // When/Then
        self::assertFalse($credential->isTokenExpired());
    }

    public function testIsTokenExpiredWithPastExpirationReturnsTrue(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setTokenExpiresAt(new \DateTime('-1 hour'));

        // When/Then
        self::assertTrue($credential->isTokenExpired());
    }

    public function testHasValidCredentialsWithCompleteCredentials(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setClientId('test-client-id')
            ->setClientSecret('test-client-secret');

        // When/Then
        self::assertTrue($credential->hasValidCredentials());
    }

    public function testHasValidCredentialsWithMissingClientId(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setClientSecret('test-client-secret');

        // When/Then
        self::assertFalse($credential->hasValidCredentials());
    }

    public function testHasValidCredentialsWithEmptyClientId(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setClientId('')
            ->setClientSecret('test-client-secret');

        // When/Then
        self::assertFalse($credential->hasValidCredentials());
    }

    public function testHasValidCredentialsWithWhitespaceOnlyClientId(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setClientId('   ')
            ->setClientSecret('test-client-secret');

        // When/Then
        self::assertFalse($credential->hasValidCredentials());
    }

    public function testHasValidTokensWithValidTokenAndFutureExpiration(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setAccessToken('test-access-token')
            ->setTokenExpiresAt(new \DateTime('+1 hour'));

        // When/Then
        self::assertTrue($credential->hasValidTokens());
    }

    public function testHasValidTokensWithExpiredToken(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setAccessToken('test-access-token')
            ->setTokenExpiresAt(new \DateTime('-1 hour'));

        // When/Then
        self::assertFalse($credential->hasValidTokens());
    }

    public function testHasValidTokensWithMissingAccessToken(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setTokenExpiresAt(new \DateTime('+1 hour'));

        // When/Then
        self::assertFalse($credential->hasValidTokens());
    }

    public function testIsActiveConnectionWithAllRequirements(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setClientId('test-client-id')
            ->setClientSecret('test-client-secret')
            ->setAccessToken('test-access-token')
            ->setTokenExpiresAt(new \DateTime('+1 hour'))
            ->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);

        // When/Then
        self::assertTrue($credential->isActiveConnection());
    }

    public function testIsActiveConnectionWithInactiveStatus(): void
    {
        // Given
        $credential = new ServiceTitanCredential();
        $credential->setClientId('test-client-id')
            ->setClientSecret('test-client-secret')
            ->setAccessToken('test-access-token')
            ->setTokenExpiresAt(new \DateTime('+1 hour'))
            ->setConnectionStatus(ServiceTitanConnectionStatus::INACTIVE);

        // When/Then
        self::assertFalse($credential->isActiveConnection());
    }

    public function testRepositoryFindByCompanyAndEnvironment(): void
    {
        // Given
        $company = $this->createTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $this->repository->save($credential, true);

        // When
        $found = $this->repository->findByCompanyAndEnvironment($company, ServiceTitanEnvironment::INTEGRATION);
        $notFound = $this->repository->findByCompanyAndEnvironment($company, ServiceTitanEnvironment::PRODUCTION);

        // Then
        self::assertSame($credential->getId(), $found?->getId());
        self::assertNull($notFound);
    }

    public function testRepositoryFindByCompany(): void
    {
        // Given
        $company = $this->createTestCompany();

        $integrationCredential = new ServiceTitanCredential();
        $integrationCredential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $this->repository->save($integrationCredential, true);

        $productionCredential = new ServiceTitanCredential();
        $productionCredential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::PRODUCTION);
        $this->repository->save($productionCredential, true);

        // When
        $credentials = $this->repository->findByCompany($company);

        // Then
        self::assertCount(2, $credentials);
        $credentialIds = array_map(fn ($c) => $c->getId(), $credentials);
        self::assertContains($integrationCredential->getId(), $credentialIds);
        self::assertContains($productionCredential->getId(), $credentialIds);
    }

    public function testRepositoryFindActiveCredentials(): void
    {
        // Given
        $company1 = $this->createTestCompany();
        $company2 = $this->createTestCompany();

        $activeCredential = new ServiceTitanCredential();
        $activeCredential->setCompany($company1)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);
        $this->repository->save($activeCredential, true);

        $inactiveCredential = new ServiceTitanCredential();
        $inactiveCredential->setCompany($company2)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setConnectionStatus(ServiceTitanConnectionStatus::INACTIVE);
        $this->repository->save($inactiveCredential, true);

        // When
        $activeCredentials = $this->repository->findActiveCredentials();

        // Then
        self::assertCount(1, $activeCredentials);
        self::assertSame($activeCredential->getId(), $activeCredentials[0]->getId());
    }

    public function testRepositoryFindExpiredTokens(): void
    {
        // Given
        $company1 = $this->createTestCompany();
        $company2 = $this->createTestCompany();

        $expiredCredential = new ServiceTitanCredential();
        $expiredCredential->setCompany($company1)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setTokenExpiresAt(new \DateTime('-1 hour'));
        $this->repository->save($expiredCredential, true);

        $validCredential = new ServiceTitanCredential();
        $validCredential->setCompany($company2)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION)
            ->setTokenExpiresAt(new \DateTime('+1 hour'));
        $this->repository->save($validCredential, true);

        // When
        $expiredTokens = $this->repository->findExpiredTokens();

        // Then
        self::assertCount(1, $expiredTokens);
        self::assertSame($expiredCredential->getId(), $expiredTokens[0]->getId());
    }

    public function testRepositoryExistsForCompanyAndEnvironment(): void
    {
        // Given
        $company = $this->createTestCompany();
        $credential = new ServiceTitanCredential();
        $credential->setCompany($company)
            ->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $this->repository->save($credential, true);

        // When/Then
        self::assertTrue($this->repository->existsForCompanyAndEnvironment($company, ServiceTitanEnvironment::INTEGRATION));
        self::assertFalse($this->repository->existsForCompanyAndEnvironment($company, ServiceTitanEnvironment::PRODUCTION));
    }

    private function createTestCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company())
            ->setIntacctId($this->faker->uuid());

        $this->companyRepository->save($company, true);

        return $company;
    }
}

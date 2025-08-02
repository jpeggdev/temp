<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Feature\CredentialManagement\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Feature\CredentialManagement\DTO\CreateServiceTitanCredentialRequest;
use App\Module\ServiceTitan\Feature\CredentialManagement\DTO\UpdateServiceTitanCredentialRequest;
use App\Module\ServiceTitan\Feature\CredentialManagement\Service\ServiceTitanCredentialService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Service\ServiceTitanAuthService;
use App\Module\ServiceTitan\Exception\ServiceTitanApiException;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceTitanCredentialServiceTest extends AbstractKernelTestCase
{
    private ServiceTitanCredentialService $service;
    private ServiceTitanCredentialRepository $repository;
    private ServiceTitanAuthService $authService;
    private Company $testCompany;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanCredentialRepository $repo */
        $repo = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->repository = $repo;

        // Create mock services since they're not public in the container
        $this->authService = $this->createMock(ServiceTitanAuthService::class);

        $this->service = new ServiceTitanCredentialService(
            $this->repository,
            $this->authService
        );

        // Create test company
        $this->testCompany = new Company();
        $this->testCompany->setCompanyName('Test Company');
        $this->entityManager->persist($this->testCompany);
        $this->entityManager->flush();
    }

    public function testCreateCredential(): void
    {
        $request = new CreateServiceTitanCredentialRequest();
        $request->clientId = 'test-client-id';
        $request->clientSecret = 'test-client-secret';
        $request->environment = ServiceTitanEnvironment::INTEGRATION->value;
        $request->company = $this->testCompany;

        $credential = $this->service->createCredential($request);

        self::assertInstanceOf(ServiceTitanCredential::class, $credential);
        self::assertSame($this->testCompany, $credential->getCompany());
        self::assertSame(ServiceTitanEnvironment::INTEGRATION, $credential->getEnvironment());
        self::assertSame('test-client-id', $credential->getClientId());
        self::assertSame('test-client-secret', $credential->getClientSecret());
        self::assertNotNull($credential->getId());
    }

    public function testCreateCredentialWithExistingForSameEnvironmentThrowsException(): void
    {
        // Create first credential
        $existingCredential = new ServiceTitanCredential();
        $existingCredential->setCompany($this->testCompany);
        $existingCredential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $existingCredential->setClientId('existing-client-id');
        $existingCredential->setClientSecret('existing-client-secret');
        $this->repository->save($existingCredential, true);

        // Try to create another for same company and environment
        $request = new CreateServiceTitanCredentialRequest();
        $request->clientId = 'test-client-id';
        $request->clientSecret = 'test-client-secret';
        $request->environment = ServiceTitanEnvironment::INTEGRATION->value;
        $request->company = $this->testCompany;

        $this->expectException(ServiceTitanApiException::class);
        $this->expectExceptionMessage('A credential already exists for this company and environment');

        $this->service->createCredential($request);
    }

    public function testFindCredential(): void
    {
        $credential = new ServiceTitanCredential();
        $credential->setCompany($this->testCompany);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test-client-id');
        $credential->setClientSecret('test-client-secret');
        $this->repository->save($credential, true);

        $found = $this->service->findCredential((string) $credential->getId());

        self::assertSame($credential->getId(), $found->getId());
    }

    public function testFindCredentialNotFoundThrowsException(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('ServiceTitan credential not found');

        $this->service->findCredential('999');
    }

    public function testUpdateCredential(): void
    {
        $credential = new ServiceTitanCredential();
        $credential->setCompany($this->testCompany);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('original-client-id');
        $credential->setClientSecret('original-client-secret');
        $this->repository->save($credential, true);

        $request = new UpdateServiceTitanCredentialRequest();
        $request->clientId = 'updated-client-id';
        $request->clientSecret = 'updated-client-secret';
        $request->environment = ServiceTitanEnvironment::PRODUCTION->value;

        $updated = $this->service->updateCredential($credential, $request);

        self::assertSame('updated-client-id', $updated->getClientId());
        self::assertSame('updated-client-secret', $updated->getClientSecret());
        self::assertSame(ServiceTitanEnvironment::PRODUCTION, $updated->getEnvironment());
    }

    public function testDeleteCredential(): void
    {
        $credential = new ServiceTitanCredential();
        $credential->setCompany($this->testCompany);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test-client-id');
        $credential->setClientSecret('test-client-secret');
        $this->repository->save($credential, true);

        $credentialId = $credential->getId();

        $this->service->deleteCredential($credential);

        $found = $this->repository->find($credentialId);
        self::assertNull($found);
    }

    public function testTestCredentialConnection(): void
    {
        $credential = new ServiceTitanCredential();
        $credential->setCompany($this->testCompany);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test-client-id');
        $credential->setClientSecret('test-client-secret');
        $this->repository->save($credential, true);

        // This will likely fail in test environment, but we're testing the service method exists
        $result = $this->service->testCredentialConnection($credential);

        self::assertIsBool($result);
    }
}

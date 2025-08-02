<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\CredentialManagement\Service;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Exception\ServiceTitanApiException;
use App\Module\ServiceTitan\Feature\CredentialManagement\DTO\CreateServiceTitanCredentialRequest;
use App\Module\ServiceTitan\Feature\CredentialManagement\DTO\UpdateServiceTitanCredentialRequest;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Service\ServiceTitanAuthService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceTitanCredentialService
{
    public function __construct(
        private readonly ServiceTitanCredentialRepository $repository,
        private readonly ServiceTitanAuthService $authService
    ) {
    }

    public function createCredential(CreateServiceTitanCredentialRequest $request): ServiceTitanCredential
    {
        $environment = ServiceTitanEnvironment::from($request->environment);

        // Check if credential already exists for this company and environment
        if ($this->repository->existsForCompanyAndEnvironment($request->company, $environment)) {
            throw new ServiceTitanApiException('A credential already exists for this company and environment');
        }

        $credential = new ServiceTitanCredential();
        $credential->setCompany($request->company);
        $credential->setEnvironment($environment);
        // For now, store credentials directly - encryption will be added later
        $credential->setClientId($request->clientId);
        $credential->setClientSecret($request->clientSecret);

        $this->repository->save($credential, true);

        return $credential;
    }

    public function findCredential(string $id): ServiceTitanCredential
    {
        $credential = $this->repository->find($id);

        if (!$credential) {
            throw new NotFoundHttpException('ServiceTitan credential not found');
        }

        return $credential;
    }

    public function updateCredential(ServiceTitanCredential $credential, UpdateServiceTitanCredentialRequest $request): ServiceTitanCredential
    {
        $environment = ServiceTitanEnvironment::from($request->environment);

        // Check if changing environment would create a conflict
        $company = $credential->getCompany();
        if ($credential->getEnvironment() !== $environment && $company) {
            if ($this->repository->existsForCompanyAndEnvironment($company, $environment)) {
                throw new ServiceTitanApiException('A credential already exists for this company and environment');
            }
        }

        $credential->setEnvironment($environment);
        // For now, store credentials directly - encryption will be added later
        $credential->setClientId($request->clientId);
        $credential->setClientSecret($request->clientSecret);

        // Reset token data when credentials change
        $credential->setAccessToken(null);
        $credential->setRefreshToken(null);
        $credential->setTokenExpiresAt(null);

        $this->repository->save($credential, true);

        return $credential;
    }

    public function deleteCredential(ServiceTitanCredential $credential): void
    {
        $this->repository->remove($credential, true);
    }

    public function testCredentialConnection(ServiceTitanCredential $credential): bool
    {
        return $this->authService->testConnection($credential);
    }
}

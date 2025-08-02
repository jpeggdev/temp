<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\DataSynchronization\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanSyncProgressService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/servicetitan')]
class GetServiceTitanSyncProgressController extends ApiController
{
    public function __construct(
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly ServiceTitanSyncProgressService $progressService,
    ) {
    }

    #[Route(
        '/sync/progress/{credentialId}',
        name: 'api_servicetitan_sync_progress_get',
        methods: ['GET']
    )]
    public function __invoke(
        int $credentialId,
        LoggedInUserDTO $loggedInUserDTO
    ): Response {
        // Find the credential
        $credential = $this->credentialRepository->find($credentialId);
        if (!$credential) {
            return $this->json(['error' => 'ServiceTitan credential not found'], Response::HTTP_NOT_FOUND);
        }

        // Verify credential belongs to user's company
        $company = $loggedInUserDTO->getActiveCompany();
        if ($credential->getCompany() !== $company) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        // Get real-time progress
        $progress = $this->progressService->getActiveProgressForCredential($credential);

        return $this->createSuccessResponse($progress);
    }
}

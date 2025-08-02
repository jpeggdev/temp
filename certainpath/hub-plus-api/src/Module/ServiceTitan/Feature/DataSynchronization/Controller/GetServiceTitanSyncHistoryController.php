<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\DataSynchronization\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanSyncService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/servicetitan')]
class GetServiceTitanSyncHistoryController extends ApiController
{
    public function __construct(
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly ServiceTitanSyncService $syncService,
    ) {
    }

    #[Route(
        '/sync/history',
        name: 'api_servicetitan_sync_history_get',
        methods: ['GET']
    )]
    public function __invoke(
        Request $request,
        LoggedInUserDTO $loggedInUserDTO
    ): Response {
        // Get pagination parameters
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 50)));

        // Get credential ID from query params
        $credentialId = $request->query->get('credentialId');
        if (!$credentialId) {
            return $this->json(['error' => 'credentialId parameter is required'], Response::HTTP_BAD_REQUEST);
        }

        // Find the credential
        $credential = $this->credentialRepository->find((int) $credentialId);
        if (!$credential) {
            return $this->json(['error' => 'ServiceTitan credential not found'], Response::HTTP_NOT_FOUND);
        }

        // Verify credential belongs to user's company
        $company = $loggedInUserDTO->getActiveCompany();
        if ($credential->getCompany() !== $company) {
            return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        }

        // Get sync history
        $history = $this->syncService->getSyncHistory($credential, $page, $limit);

        return $this->createSuccessResponse($history);
    }
}

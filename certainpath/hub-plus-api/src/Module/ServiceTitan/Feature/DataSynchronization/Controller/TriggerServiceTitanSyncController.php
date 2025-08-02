<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\DataSynchronization\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Feature\DataSynchronization\Service\ServiceTitanSyncService;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/servicetitan')]
class TriggerServiceTitanSyncController extends ApiController
{
    public function __construct(
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly ServiceTitanSyncService $syncService,
    ) {
    }

    #[Route(
        '/sync/{credentialId}',
        name: 'api_servicetitan_sync_trigger',
        methods: ['POST']
    )]
    public function __invoke(
        int $credentialId,
        Request $request,
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

        // Parse request data
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Invalid JSON payload'], Response::HTTP_BAD_REQUEST);
        }

        // Validate data type
        $dataTypeValue = $data['dataType'] ?? null;
        if (!$dataTypeValue || !in_array($dataTypeValue, ServiceTitanSyncDataType::VALUES, true)) {
            return $this->json(['error' => 'Invalid or missing dataType'], Response::HTTP_BAD_REQUEST);
        }

        $dataType = ServiceTitanSyncDataType::from($dataTypeValue);

        // Validate sync type
        $syncTypeValue = $data['syncType'] ?? ServiceTitanSyncType::MANUAL->value;
        if (!in_array($syncTypeValue, ServiceTitanSyncType::VALUES, true)) {
            return $this->json(['error' => 'Invalid syncType'], Response::HTTP_BAD_REQUEST);
        }

        $syncType = ServiceTitanSyncType::from($syncTypeValue);

        // Use the service to trigger the sync
        try {
            $syncLog = $this->syncService->triggerSync($credential, $dataType, $syncType);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->createSuccessResponse([
            'syncLogId' => $syncLog->getId(),
            'status' => 'queued',
            'dataType' => $dataType->value,
            'syncType' => $syncType->value,
            'startedAt' => $syncLog->getStartedAt()->format('c'),
        ], Response::HTTP_ACCEPTED);
    }
}

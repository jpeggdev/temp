<?php

declare(strict_types=1);

namespace App\Controller\Unification\BulkBatchesStatus;

use App\Controller\ApiController;
use App\DTO\Request\BulkUpdateBatchesStatusQueryDTO;
use App\Exception\BatchArchiveException;
use App\Security\Voter\BulkBatchStatusVoterRole;
use App\Service\Unification\BulkUpdateBatchesStatusService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class BulkUpdateBatchesStatusController extends ApiController
{
    public function __construct(
        private readonly BulkUpdateBatchesStatusService $bulkUpdateBatchesStatusService,
    ) {
    }

    /**
     * @throws BatchArchiveException
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/batches/bulk-update-status', name: 'api_batches_bulk_update_status', methods: ['PATCH'])]
    public function __invoke(
        #[MapRequestPayload] BulkUpdateBatchesStatusQueryDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(BulkBatchStatusVoterRole::BULK_UPDATE);

        $this->bulkUpdateBatchesStatusService->bulkUpdateStatus(
            $dto->year,
            $dto->week,
            $dto->status
        );

        return $this->createSuccessResponse([
            'message' => 'Batches status has been updated.',
        ]);
    }
}

<?php

namespace App\Controller\API\BatchStatus;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Repository\BatchStatusRepository;
use App\Resources\BatchStatusResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetBatchStatusController extends ApiController
{
    public function __construct(
        private readonly BatchStatusRepository $batchStatusRepository,
        private readonly BatchStatusResource $batchStatusResource
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     */
    #[Route('/api/batch-status/{id}', name: 'api_batch_status_get', methods: ['GET'])]
    public function __invoke(
        int $id
    ): Response {
        $batchStatus = $this->batchStatusRepository->findOneById($id);
        if (!$batchStatus) {
            throw new BatchStatusNotFoundException();
        }

        $batchStatusData = $this->batchStatusResource->transformItem($batchStatus);

        return $this->createJsonSuccessResponse($batchStatusData);
    }
}

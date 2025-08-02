<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\Exceptions\DomainException\Batch\BatchCannotBeArchivedException;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Repository\BatchRepository;
use App\Resources\BatchResource;
use App\Services\BatchService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ArchiveBatchController extends ApiController
{
    public function __construct(
        private readonly BatchRepository $batchRepository,
        private readonly BatchService $batchService,
        private readonly BatchResource $batchResource,
    ) {
    }

    /**
     * @throws BatchNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws BatchCannotBeArchivedException
     */
    #[Route('/api/batch/archive/{id}', name: 'api_batch_archive', methods: ['PATCH'])]
    public function __invoke(int $id): Response
    {
        $batch = $this->batchRepository->findByIdOrFail($id);
        $this->batchService->archiveBatch($batch);

        $batchData = $this->batchResource->transformItem($batch);

        return $this->createJsonSuccessResponse($batchData);
    }
}

<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Repository\BatchRepository;
use App\Resources\BatchResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetBatchController extends ApiController
{
    public function __construct(
        private readonly BatchRepository $batchRepository,
        private readonly BatchResource $batchResource
    ) {
    }

    /**
     * @throws BatchNotFoundException
     */
    #[Route('/api/batch/{id}', name: 'api_batch_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $batch = $this->batchRepository->findById($id);
        if (!$batch) {
            throw new BatchNotFoundException();
        }

        $batchData = $this->batchResource->transformItem($batch);

        return $this->createJsonSuccessResponse($batchData);
    }
}

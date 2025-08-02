<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\DTO\Request\Batch\PatchBatchDTO;
use App\Exceptions\NotFoundException\BatchNotFoundException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Repository\BatchRepository;
use App\Resources\BatchResource;
use App\Services\BatchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class PatchBatchController extends ApiController
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
     */
    #[Route('/api/batch/{id}', name: 'api_batch_patch', methods: ['PATCH'])]
    public function __invoke(
        int $id,
        Request $request,
        #[MapRequestPayload] PatchBatchDTO $patchDTO = new PatchBatchDTO(),
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $batch = $this->batchRepository->findById($id);
        if (!$batch) {
            throw new BatchNotFoundException();
        }
        $this->batchService->patchBatch($batch, $patchDTO);

        $includes = $paginationDTO->includes;
        $batchData = $this->batchResource->transformItem($batch, $includes);

        return $this->createJsonSuccessResponse($batchData);
    }
}

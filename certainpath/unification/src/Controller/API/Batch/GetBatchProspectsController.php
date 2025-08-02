<?php

namespace App\Controller\API\Batch;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Repository\ProspectRepository;
use App\Resources\ProspectResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetBatchProspectsController extends ApiController
{
    public function __construct(
        private readonly ProspectRepository $prospectRepository,
        private readonly ProspectResource $prospectResource,
    ) {
    }

    #[Route('/api/batch/{id}/prospects', name: 'api_batch_prospects_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $page = $paginationDTO->page;
        $perPage = $paginationDTO->perPage;
        $sortOrder = $paginationDTO->sortOrder;

        $prospects = $this->prospectRepository->paginateAllByBatchId($id, $page, $perPage, $sortOrder);
        $batchProspectsData = $this->prospectResource->transformCollection($prospects['items']);

        return $this->createJsonSuccessResponse($batchProspectsData, $prospects);
    }
}

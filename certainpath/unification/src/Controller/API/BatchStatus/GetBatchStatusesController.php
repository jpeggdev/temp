<?php

namespace App\Controller\API\BatchStatus;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Repository\BatchStatusRepository;
use App\Resources\BatchStatusResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetBatchStatusesController extends ApiController
{
    public function __construct(
        private readonly BatchStatusRepository $batchStatusRepository,
        private readonly BatchStatusResource $batchStatusResource,
    ) {
    }

    #[Route('/api/batch-statuses', name: 'api_batch_statuses_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $page = $paginationDTO->page;
        $perPage = $paginationDTO->perPage;
        $sortOrder = $paginationDTO->sortOrder;

        $pagination = $this->batchStatusRepository->paginateAll($page, $perPage, $sortOrder);
        $batchStatusData = $this->batchStatusResource->transformCollection($pagination['items']);

        return $this->createJsonSuccessResponse($batchStatusData, $pagination);
    }
}

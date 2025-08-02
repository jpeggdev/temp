<?php

namespace App\Controller\API\CampaignBatch;

use App\Controller\API\ApiController;
use App\DTO\Query\Batch\BatchesQueryDTO;
use App\Repository\BatchRepository;
use App\Resources\BatchResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignBatchesController extends ApiController
{
    public function __construct(
        private readonly BatchRepository  $batchRepository,
        private readonly BatchResource $batchResource
    ) {
    }

    #[Route('/api/campaign/{id}/batches', name: 'api_campaign_batches_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        #[MapQueryString] BatchesQueryDTO $queryDTO = new BatchesQueryDTO(),
    ): Response {
        $page = $queryDTO->page;
        $perPage = $queryDTO->perPage;
        $includes = $queryDTO->includes;
        $sortOrder = $queryDTO->sortOrder;
        $batchStatusId = $queryDTO->batchStatusId;

        $campaignBatches = $this->batchRepository->paginateAllByCampaignIdAndStatusId(
            $id,
            $batchStatusId,
            $page,
            $perPage,
            $sortOrder
        );

        $campaignBatchesData = $this->batchResource->transformCollection(
            $campaignBatches['items'],
            $includes
        );

        return $this->createJsonSuccessResponse($campaignBatchesData, $campaignBatches);
    }
}

<?php

namespace App\Controller\API\CampaignIterationStatus;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Repository\CampaignIterationStatusRepository;
use App\Resources\CampaignIterationStatusResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignIterationStatusesController extends ApiController
{
    public function __construct(
        private readonly CampaignIterationStatusRepository $campaignIterationStatusRepository,
        private readonly CampaignIterationStatusResource $campaignIterationStatusResource,
    ) {
    }

    #[Route('/api/campaign-iteration-statuses', name: 'api_campaign_iteration_statuses_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $page = $paginationDTO->page;
        $perPage = $paginationDTO->perPage;
        $sortOrder = $paginationDTO->sortOrder;

        $pagination = $this->campaignIterationStatusRepository->paginateAll($page, $perPage, $sortOrder);
        $campaignStatusesData = $this->campaignIterationStatusResource->transformCollection($pagination['items']);

        return $this->createJsonSuccessResponse($campaignStatusesData, $pagination);
    }
}

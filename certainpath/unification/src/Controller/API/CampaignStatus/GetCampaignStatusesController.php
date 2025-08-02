<?php

namespace App\Controller\API\CampaignStatus;

use App\Controller\API\ApiController;
use App\DTO\Query\PaginationDTO;
use App\Repository\CampaignStatusRepository;
use App\Resources\CampaignStatusResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignStatusesController extends ApiController
{
    public function __construct(
        private readonly CampaignStatusRepository $campaignStatusRepository,
        private readonly CampaignStatusResource $campaignStatusResource
    ) {
    }

    #[Route('/api/campaign-statuses', name: 'api_campaign_statuses_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] PaginationDTO $paginationDTO = new PaginationDTO(),
    ): Response {
        $page = $paginationDTO->page;
        $perPage = $paginationDTO->perPage;
        $sortOrder = $paginationDTO->sortOrder;

        $pagination = $this->campaignStatusRepository->paginateAll($page, $perPage, $sortOrder);
        $campaignStatusesData = $this->campaignStatusResource->transformCollection($pagination['items']);

        return $this->createJsonSuccessResponse($campaignStatusesData);
    }
}

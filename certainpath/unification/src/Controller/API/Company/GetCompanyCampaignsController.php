<?php

namespace App\Controller\API\Company;

use App\Controller\API\ApiController;
use App\DTO\Query\Campaign\CampaignQueryDTO;
use App\Repository\CampaignRepository;
use App\Resources\CampaignResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class GetCompanyCampaignsController extends ApiController
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly CampaignResource $campaignResource,
    ) {
    }

    #[Route('/api/company/{identifier}/campaigns', name: 'api_company_campaigns_get', methods: ['GET'])]
    public function __invoke(
        string $identifier,
        #[MapQueryString] CampaignQueryDTO $queryDTO = new CampaignQueryDTO(),
    ): Response {
        $page = $queryDTO->page;
        $perPage = $queryDTO->perPage;
        $includes = $queryDTO->includes;
        $sortOrder = $queryDTO->sortOrder;
        $campaignStatusId = $queryDTO->campaignStatusId;

        $pagination = $this->campaignRepository->paginateAllByCompanyIdentifierAndStatusId(
            $identifier,
            $campaignStatusId,
            $page,
            $perPage,
            $sortOrder,
        );
        $campaignsData = $this->campaignResource->transformCollection($pagination['items'], $includes);

        return $this->createJsonSuccessResponse($campaignsData, $pagination);
    }
}

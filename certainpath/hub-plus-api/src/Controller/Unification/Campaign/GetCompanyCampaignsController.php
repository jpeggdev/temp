<?php

declare(strict_types=1);

namespace App\Controller\Unification\Campaign;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Campaign\GetCompanyCampaignsQueryDTO;
use App\Service\Unification\GetCompanyCampaignsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCompanyCampaignsController extends ApiController
{
    public function __construct(private readonly GetCompanyCampaignsService $getCompanyCampaignsService)
    {
    }

    #[Route('/company/campaigns', name: 'api_company_campaigns_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetCompanyCampaignsQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $campaignsResponse = $this->getCompanyCampaignsService->getCompanyCampaigns(
            $loggedInUserDTO->getActiveCompany()->getIntacctId(),
            $queryDto->page,
            $queryDto->perPage,
            strtoupper($queryDto->sortOrder),
            $queryDto->campaignStatusId
        );

        return $this->createSuccessResponse(
            $campaignsResponse['campaigns'],
            $campaignsResponse['totalCount']
        );
    }
}

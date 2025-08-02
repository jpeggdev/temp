<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Unification\GetCampaignStatusesService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCampaignStatusesController extends ApiController
{
    public function __construct(private readonly GetCampaignStatusesService $getCampaignStatusesService)
    {
    }

    #[Route('/campaign-statuses', name: 'api_campaign_statuses_get', methods: ['GET'])]
    public function __invoke(): Response
    {
        $campaignStatusesResponse = $this->getCampaignStatusesService->getCampaignStatuses();

        return $this->createSuccessResponse(
            $campaignStatusesResponse['campaignStatuses']
        );
    }
}

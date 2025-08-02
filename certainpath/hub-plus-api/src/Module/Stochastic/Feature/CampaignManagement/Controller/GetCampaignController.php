<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\GetCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCampaignController extends ApiController
{
    public function __construct(private readonly GetCampaignService $getCampaignService)
    {
    }

    #[Route('/campaign/{id}', name: 'api_campaign_get', methods: ['GET'])]
    public function __invoke(
        int $id,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $campaignResponse = $this->getCampaignService->getCampaign($id);

        return $this->createSuccessResponse($campaignResponse);
    }
}

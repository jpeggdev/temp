<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\Request\UpdateCampaignDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\UpdateCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateCampaignController extends ApiController
{
    public function __construct(private readonly UpdateCampaignService $updateCampaignService)
    {
    }

    #[Route('/campaign/{id}', name: 'api_campaign_patch', methods: ['PATCH'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] UpdateCampaignDTO $patchCampaignDTO,
    ): Response {
        $campaignResponse = $this->updateCampaignService->updateCampaign($id, $patchCampaignDTO);

        return $this->createSuccessResponse($campaignResponse);
    }
}

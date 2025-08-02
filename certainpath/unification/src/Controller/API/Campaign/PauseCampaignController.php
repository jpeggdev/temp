<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Repository\CampaignRepository;
use App\Services\Campaign\PauseCampaignService;
use App\Resources\CampaignResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PauseCampaignController extends ApiController
{
    public function __construct(
        private readonly CampaignResource $campaignResource,
        private readonly CampaignRepository $campaignRepository,
        private readonly PauseCampaignService $pauseCampaignService,
    ) {
    }

    /**
     * @throws CampaignNotFoundException
     * @throws BatchStatusNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignIterationStatusNotFoundException
     */
    #[Route('/api/campaign/pause/{id}', name: 'api_campaign_pause', methods: ['PATCH'])]
    public function __invoke(int $id): Response
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($id);

        $this->pauseCampaignService->pause($campaign);
        $campaignData = $this->campaignResource->transformItem($campaign);

        return $this->createJsonSuccessResponse($campaignData);
    }
}

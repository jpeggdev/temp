<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\Exceptions\DomainException\Campaign\CampaignStopFailedException;
use App\Exceptions\NotFoundException\BatchStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignIterationStatusNotFoundException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Exceptions\NotFoundException\CampaignStatusNotFoundException;
use App\Repository\CampaignRepository;
use App\Services\StopCampaignService;
use App\Resources\CampaignResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StopCampaignController extends ApiController
{
    public function __construct(
        private readonly StopCampaignService $stopCampaignService,
        private readonly CampaignResource $campaignResource,
        private readonly CampaignRepository $campaignRepository,
    ) {
    }

    /**
     * @throws BatchStatusNotFoundException
     * @throws CampaignNotFoundException
     * @throws CampaignStatusNotFoundException
     * @throws CampaignStopFailedException
     * @throws CampaignIterationStatusNotFoundException
     */
    #[Route('/api/campaign/stop/{id}', name: 'api_campaign_stop', methods: ['PATCH'])]
    public function __invoke(int $id): Response
    {
        $campaign = $this->campaignRepository->findOneByIdOrFail($id);
        $this->stopCampaignService->stop($campaign);
        $campaignData = $this->campaignResource->transformItem($campaign);

        return $this->createJsonSuccessResponse($campaignData);
    }
}

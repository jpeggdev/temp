<?php

namespace App\Controller\API\CampaignStatus;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Repository\CampaignIterationStatusRepository;
use App\Resources\CampaignIterationStatusResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignStatusController extends ApiController
{
    public function __construct(
        private readonly CampaignIterationStatusRepository $campaignStatusRepository,
        private readonly CampaignIterationStatusResource $campaignIterationStatusResource
    ) {
    }

    /**
     * @throws CampaignIterationNotFoundException
     */
    #[Route('/api/campaign-status/{id}', name: 'api_campaign_status_get', methods: ['GET'])]
    public function __invoke(
        int $id
    ): Response {
        $campaignStatus = $this->campaignStatusRepository->findById($id);
        if (!$campaignStatus) {
            throw new CampaignIterationNotFoundException();
        }

        $campaignStatusData = $this->campaignIterationStatusResource->transformItem($campaignStatus);

        return $this->createJsonSuccessResponse($campaignStatusData);
    }
}

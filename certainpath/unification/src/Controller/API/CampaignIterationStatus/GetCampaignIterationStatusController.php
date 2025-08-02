<?php

namespace App\Controller\API\CampaignIterationStatus;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\CampaignIterationNotFoundException;
use App\Repository\CampaignIterationStatusRepository;
use App\Resources\CampaignIterationStatusResource;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignIterationStatusController extends ApiController
{
    public function __construct(
        private readonly CampaignIterationStatusRepository $campaignIterationStatusRepository,
        private readonly CampaignIterationStatusResource $campaignIterationStatusResource,
    ) {
    }

    /**
     * @throws CampaignIterationNotFoundException
     */
    #[Route('/api/campaign-iteration-status/{id}', name: 'api_campaign_iteration_status_get', methods: ['GET'])]
    public function __invoke(
        int $id
    ): Response {
        $campaignStatus = $this->campaignIterationStatusRepository->findById($id);
        if (!$campaignStatus) {
            throw new CampaignIterationNotFoundException();
        }

        $campaignStatusData = $this->campaignIterationStatusResource->transformItem($campaignStatus);

        return $this->createJsonSuccessResponse($campaignStatusData);
    }
}

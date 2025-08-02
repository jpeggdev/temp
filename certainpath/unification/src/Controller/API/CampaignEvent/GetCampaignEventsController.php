<?php

namespace App\Controller\API\CampaignEvent;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Resources\CampaignEventResource;
use App\Services\CampaignEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignEventsController extends ApiController
{
    public function __construct(
        private readonly CampaignEventService $campaignEventService,
        private readonly CampaignEventResource $campaignEventResource
    ) {
    }

    /**
     * @throws CampaignNotFoundException
     */
    #[Route('/api/campaign/{id}/campaign-events', name: 'api_campaign_events_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $campaignEvents = $this->campaignEventService->getCampaignEvents($id);
        $campaignEventsData = $this->campaignEventResource->transformCollection($campaignEvents);

        return $this->createJsonSuccessResponse($campaignEventsData);
    }
}

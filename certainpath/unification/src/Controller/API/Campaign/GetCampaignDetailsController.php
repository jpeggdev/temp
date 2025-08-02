<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\Exceptions\DomainException\Campaign\FailedToGetCampaignDetailsException;
use App\Exceptions\NotFoundException\CampaignNotFoundException;
use App\Services\Campaign\GetCampaignDetailsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignDetailsController extends ApiController
{
    public function __construct(
        private readonly GetCampaignDetailsService $getCampaignDetailsService,
    ) {
    }

    /**
     * @throws \JsonException
     * @throws CampaignNotFoundException
     * @throws FailedToGetCampaignDetailsException
     */
    #[Route('/api/campaign/{id}/details', name: 'api_campaign_details_get', methods: ['GET'])]
    public function __invoke(
        Request $request,
    ): Response {
        $id = $request->get('id');
        $campaignDetailsData = $this->getCampaignDetailsService->getCampaignDetails($id);

        return $this->createJsonSuccessResponse($campaignDetailsData);
    }
}

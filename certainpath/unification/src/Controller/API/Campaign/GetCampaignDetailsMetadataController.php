<?php

namespace App\Controller\API\Campaign;

use App\Controller\API\ApiController;
use App\Exceptions\NotFoundException\ProspectFilterRuleNotFoundException;
use App\Services\DetailsMetadata\CampaignDetailsMetadataService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetCampaignDetailsMetadataController extends ApiController
{
    public function __construct(
        private readonly CampaignDetailsMetadataService $campaignDetailsMetadataService,
    ) {
    }

    /**
     * @throws ProspectFilterRuleNotFoundException
     */
    #[Route(
        '/api/campaign-details-metadata',
        name: 'api_campaign_details_metadata_get',
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $detailsMetadata = $this->campaignDetailsMetadataService->getDetailsMetadata();

        return $this->createJsonSuccessResponse($detailsMetadata);
    }
}

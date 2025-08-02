<?php

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\Exception\APICommunicationException;
use App\Exception\CampaignDetailsMetadataNotFoundException;
use App\Module\Stochastic\Feature\CampaignManagement\Service\GetCampaignDetailsMetadataService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetCampaignDetailsMetadataController extends ApiController
{
    public function __construct(
        private readonly GetCampaignDetailsMetadataService $getCampaignDetailsMetadataService,
    ) {
    }

    /**
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws CampaignDetailsMetadataNotFoundException
     */
    #[Route(
        '/campaign-details-metadata',
        name: 'api_campaign_details_metadata_get',
        methods: ['GET']
    )]
    public function __invoke(Request $request): Response
    {
        $detailsMetadata = $this->getCampaignDetailsMetadataService->getDetailsMetadata();

        return $this->createSuccessResponse($detailsMetadata);
    }
}

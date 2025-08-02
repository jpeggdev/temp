<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\APICommunicationException;
use App\Exception\NotFoundException\CampaignNotFoundException;
use App\Module\Stochastic\Feature\CampaignManagement\Service\GetCampaignDetailsService;
use App\Module\Stochastic\Feature\CampaignManagement\Voter\CampaignDetailsVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

#[Route(path: '/api/private')]
class GetCampaignDetailsController extends ApiController
{
    public function __construct(
        private readonly GetCampaignDetailsService $getCampaignDetailsService,
    ) {
    }

    /**
     * @throws CampaignNotFoundException
     * @throws APICommunicationException
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    #[Route('/campaign/{campaignId}/details', name: 'api_campaign_details_get', methods: ['GET'])]
    public function __invoke(
        int $campaignId,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->denyAccessUnlessGranted(CampaignDetailsVoter::VIEW, $campaignId);
        $campaignResponse = $this->getCampaignDetailsService->getDetails(
            $campaignId,
        );

        return $this->createSuccessResponse($campaignResponse);
    }
}

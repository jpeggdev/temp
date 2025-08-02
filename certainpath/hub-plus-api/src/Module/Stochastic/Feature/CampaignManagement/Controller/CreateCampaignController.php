<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\Unification\CampaignCreationException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Request\CreateCampaignDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\CreateCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class CreateCampaignController extends ApiController
{
    public function __construct(
        private readonly CreateCampaignService $createCampaignService,
    ) {
    }

    /**
     * @throws CampaignCreationException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/campaign/create', name: 'api_campaign_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateCampaignDTO $createCampaignRequestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $campaignResponse = $this->createCampaignService->createCampaign(
            $createCampaignRequestDTO,
            $loggedInUserDTO->getActiveCompany()->getIntacctId()
        );

        return $this->createSuccessResponse($campaignResponse);
    }
}

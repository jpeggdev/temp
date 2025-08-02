<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\CampaignPauseException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Request\PauseCampaignDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\PauseCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class PauseCampaignController extends ApiController
{
    public function __construct(
        private readonly PauseCampaignService $pauseCampaignService,
    ) {
    }

    /**
     * @throws CampaignPauseException
     * @throws ServerExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/campaign/pause', name: 'api_campaign_pause', methods: ['PATCH'])]
    public function __invoke(
        #[MapRequestPayload] PauseCampaignDTO $pauseCampaignDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->pauseCampaignService->pauseCampaign($pauseCampaignDTO->campaignId);

        return $this->createSuccessResponse([
            'message' => sprintf('Campaign %d has been paused.', $pauseCampaignDTO->campaignId),
        ]);
    }
}

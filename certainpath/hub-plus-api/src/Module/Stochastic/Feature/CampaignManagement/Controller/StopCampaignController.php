<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\StopCampaignDTO;
use App\Exception\CampaignStopException;
use App\Module\Stochastic\Feature\CampaignManagement\Service\StopCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class StopCampaignController extends ApiController
{
    public function __construct(
        private readonly StopCampaignService $stopCampaignService,
    ) {
    }

    /**
     * @throws CampaignStopException
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/campaign/stop', name: 'api_campaign_stop', methods: ['PATCH'])]
    public function __invoke(
        #[MapRequestPayload] StopCampaignDTO $stopCampaignDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->stopCampaignService->stopCampaign($stopCampaignDTO->campaignId);

        return $this->createSuccessResponse([
            'message' => sprintf('Campaign %d has been stopped (archived).', $stopCampaignDTO->campaignId),
        ]);
    }
}

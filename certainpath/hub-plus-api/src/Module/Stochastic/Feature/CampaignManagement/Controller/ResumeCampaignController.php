<?php

declare(strict_types=1);

namespace App\Module\Stochastic\Feature\CampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Exception\CampaignResumeException;
use App\Module\Stochastic\Feature\CampaignManagement\DTO\Request\ResumeCampaignDTO;
use App\Module\Stochastic\Feature\CampaignManagement\Service\ResumeCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[Route(path: '/api/private')]
class ResumeCampaignController extends ApiController
{
    public function __construct(
        private readonly ResumeCampaignService $resumeCampaignService,
    ) {
    }

    /**
     * @throws CampaignResumeException
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws TransportExceptionInterface
     * @throws RedirectionExceptionInterface
     */
    #[Route('/campaign/resume', name: 'api_campaign_resume', methods: ['PATCH'])]
    public function __invoke(
        #[MapRequestPayload] ResumeCampaignDTO $resumeCampaignDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $this->resumeCampaignService->resumeCampaign($resumeCampaignDTO->campaignId);

        return $this->createSuccessResponse([
            'message' => sprintf('Campaign %d has been resumed.', $resumeCampaignDTO->campaignId),
        ]);
    }
}

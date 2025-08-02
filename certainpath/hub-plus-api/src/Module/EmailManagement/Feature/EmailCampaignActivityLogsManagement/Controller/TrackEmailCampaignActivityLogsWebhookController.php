<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\ProcessEmailCampaignWebhookService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/public/webhook')]
class TrackEmailCampaignActivityLogsWebhookController extends ApiController
{
    public function __construct(
        private readonly ProcessEmailCampaignWebhookService $processEmailCampaignWebhookService,
    ) {
    }

    /**
     * @throws \JsonException
     */
    #[Route('/track-email-campaign-activity', name: 'api_email_campaign_webhook', methods: ['POST'])]
    public function __invoke(
        Request $request,
    ): Response {
        $this->processEmailCampaignWebhookService->processWebhook($request);

        return $this->createSuccessResponse([]);
    }
}

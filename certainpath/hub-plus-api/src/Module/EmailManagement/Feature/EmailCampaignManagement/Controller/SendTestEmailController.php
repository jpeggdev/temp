<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\SendCampaignEmailDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\SendCampaignEmailService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class SendTestEmailController extends ApiController
{
    public function __construct(
        private readonly SendCampaignEmailService $sendTestEmailService,
    ) {
    }

    #[Route(
        '/email-campaign/send-test-email',
        name: 'api_email_campaign_send_test_email',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] SendCampaignEmailDTO $sendTestEmailDTO,
    ): Response {
        $result = $this->sendTestEmailService->sendEmail($sendTestEmailDTO);
        $message = $result
            ? 'Test email successfully sent'
            : 'Failed to send test email';

        return $this->createSuccessResponse(['message' => $message]);
    }
}

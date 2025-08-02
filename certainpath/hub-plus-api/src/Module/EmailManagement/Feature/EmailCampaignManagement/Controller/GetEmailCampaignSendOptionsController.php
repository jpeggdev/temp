<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\GetEmailCampaignSendOptionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignSendOptionsController extends ApiController
{
    public const string SEND_OPTION_SAVE_AS_DRAFT = 'Save as Draft';
    public const string SEND_OPTION_SEND_IMMEDIATELY = 'Send Immediately';
    public const string SEND_OPTION_SCHEDULE_FOR_LATER = 'Schedule For Later';

    public function __construct(
        private readonly GetEmailCampaignSendOptionService $getEmailCampaignSendOptionService,
    ) {
    }

    #[Route('/email-campaign-send-options', name: 'api_email_campaign_send_options', methods: ['GET'])]
    public function __invoke(): Response
    {
        $sendOptions = $this->getEmailCampaignSendOptionService->getSendOptions();

        return $this->createSuccessResponse($sendOptions);
    }
}

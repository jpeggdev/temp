<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query\GetEmailCampaignRecipientCountDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\GetEmailCampaignRecipientCountService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignRecipientCountController extends ApiController
{
    public function __construct(
        private readonly GetEmailCampaignRecipientCountService $getEmailCampaignRecipientCountService,
    ) {
    }

    #[Route(
        '/email-campaign-recipient-count',
        name: 'api_email_campaign_recipient_count_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetEmailCampaignRecipientCountDTO $requestDTO,
    ): Response {
        $recipientCount = $this->getEmailCampaignRecipientCountService->getRecipientCount($requestDTO);

        return $this->createSuccessResponse($recipientCount);
    }
}

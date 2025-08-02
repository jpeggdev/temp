<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\GetEmailCampaignActivityLogsDTO;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Service\GetEmailCampaignActivityLogService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignActivityLogsController extends ApiController
{
    public function __construct(
        private readonly GetEmailCampaignActivityLogService $getEmailCampaignStatusService,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route(
        '/email-campaign-activity-logs',
        name: 'api_email_campaign_activity_logs_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetEmailCampaignActivityLogsDTO $requestDTO = new GetEmailCampaignActivityLogsDTO(),
    ): Response {
        $emailCampaignActivityLogsData = $this->getEmailCampaignStatusService->getEmailCampaignActivityLogs(
            $requestDTO
        );

        return $this->createSuccessResponse(
            $emailCampaignActivityLogsData['emailCampaignActivityLogs'],
            $emailCampaignActivityLogsData['totalCount'],
        );
    }
}

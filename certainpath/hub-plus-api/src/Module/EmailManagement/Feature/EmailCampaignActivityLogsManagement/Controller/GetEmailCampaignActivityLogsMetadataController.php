<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\DTO\Query\GetEmailCampaignActivityLogsMetadataDTO;
use App\Module\EmailManagement\Feature\EmailCampaignActivityLogsManagement\Service\GetEmailCampaignActivityLogsMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignActivityLogsMetadataController extends ApiController
{
    public function __construct(
        private readonly GetEmailCampaignActivityLogsMetadataService $getEmailCampaignActivityLogsMetadataService,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    #[Route(
        '/email-campaign-activity-logs/metadata',
        name: 'api_email_campaign_activity_log_metadata_get',
        methods: ['GET']
    )]
    public function __invoke(
        #[MapQueryString] GetEmailCampaignActivityLogsMetadataDTO $queryDTO,
    ): Response {
        $emailCampaignActivityLogsData = $this->getEmailCampaignActivityLogsMetadataService->getMetadata(
            $queryDTO
        );

        return $this->createSuccessResponse($emailCampaignActivityLogsData);
    }
}

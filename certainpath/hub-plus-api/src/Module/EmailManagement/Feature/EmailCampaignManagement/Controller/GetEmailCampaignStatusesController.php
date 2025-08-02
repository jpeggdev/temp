<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\DTO\Query\EmailCampaignStatuses\GetEmailCampaignStatusesDTO;
use App\Service\EmailCampaignStatus\GetEmailCampaignStatusService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignStatusesController extends ApiController
{
    public function __construct(
        private readonly GetEmailCampaignStatusService $getEmailCampaignStatusService,
    ) {
    }

    #[Route('/email-campaign-statuses', name: 'api_email_campaign_statuses_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEmailCampaignStatusesDTO $requestDTO = new GetEmailCampaignStatusesDTO(),
    ): Response {
        $emailCampaignsData = $this->getEmailCampaignStatusService->getEmailCampaignStatuses($requestDTO);

        return $this->createSuccessResponse(
            $emailCampaignsData['emailCampaignStatuses'],
            $emailCampaignsData['totalCount'],
        );
    }
}

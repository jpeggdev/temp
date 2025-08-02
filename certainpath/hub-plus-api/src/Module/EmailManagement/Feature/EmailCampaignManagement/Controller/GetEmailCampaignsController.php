<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Query\GetEmailCampaignsDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\GetEmailCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignsController extends ApiController
{
    public function __construct(
        private readonly GetEmailCampaignService $getEmailCampaignService,
    ) {
    }

    #[Route('/email-campaigns', name: 'api_email_campaigns_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEmailCampaignsDTO $requestDTO,
    ): Response {
        $emailCampaignsData = $this->getEmailCampaignService->getEmailCampaigns($requestDTO);

        return $this->createSuccessResponse(
            $emailCampaignsData['emailCampaigns'],
            $emailCampaignsData['totalCount'],
        );
    }
}

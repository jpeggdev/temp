<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\GetEmailCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailCampaignController extends ApiController
{
    public function __construct(
        private readonly GetEmailCampaignService $getEmailCampaignService,
    ) {
    }

    #[Route('/email-campaign/{id}', name: 'api_email_campaign_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $emailCampaignData = $this->getEmailCampaignService->getEmailCampaign($id);

        return $this->createSuccessResponse($emailCampaignData);
    }
}

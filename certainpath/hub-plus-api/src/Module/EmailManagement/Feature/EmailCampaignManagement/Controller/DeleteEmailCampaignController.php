<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\DeleteEmailCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEmailCampaignController extends ApiController
{
    public function __construct(
        private readonly DeleteEmailCampaignService $deleteEmailCampaignService,
    ) {
    }

    #[Route(
        '/email-campaign/{id}/delete',
        name: 'api_email_campaign_delete',
        methods: ['DELETE']
    )]
    public function __invoke(int $id): Response
    {
        $deletedEmailCampaign = $this->deleteEmailCampaignService->deleteEmailCampaign($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Email Campaign %d has been deleted.', $deletedEmailCampaign->id),
        ]);
    }
}

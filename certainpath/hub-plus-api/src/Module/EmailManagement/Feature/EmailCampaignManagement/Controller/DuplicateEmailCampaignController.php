<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\DuplicateEmailCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DuplicateEmailCampaignController extends ApiController
{
    public function __construct(
        private readonly DuplicateEmailCampaignService $duplicateEmailCampaignService,
    ) {
    }

    #[Route(
        '/email-campaign/{id}/duplicate',
        name: 'api_email_campaign_duplicate',
        methods: ['DELETE']
    )]
    public function __invoke(int $id): Response
    {
        $deletedEmailCampaign = $this->duplicateEmailCampaignService->duplicateEmailCampaign($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Email Campaign %d has been deleted.', $deletedEmailCampaign->id),
        ]);
    }
}

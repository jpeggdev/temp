<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Exception\UnsupportedSendOptionException;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\CreateUpdateEmailCampaignDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\UpdateEmailCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmailCampaignController extends ApiController
{
    public function __construct(
        private readonly UpdateEmailCampaignService $updateEmailCampaignService,
    ) {
    }

    /**
     * @throws UnsupportedSendOptionException
     */
    #[Route('/email-campaign/{id}/update', name: 'api_email_campaign_update', methods: ['PUT', 'PATCH'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] CreateUpdateEmailCampaignDTO $requestDTO,
    ): Response {
        $this->updateEmailCampaignService->updateCampaign($id, $requestDTO);

        return $this->createSuccessResponse([]);
    }
}

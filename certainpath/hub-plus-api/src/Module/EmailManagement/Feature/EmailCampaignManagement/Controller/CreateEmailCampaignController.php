<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailCampaignManagement\Controller;

use App\Controller\ApiController;
use App\Exception\UnsupportedSendOptionException;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\DTO\Request\CreateUpdateEmailCampaignDTO;
use App\Module\EmailManagement\Feature\EmailCampaignManagement\Service\CreateEmailCampaignService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEmailCampaignController extends ApiController
{
    public function __construct(
        private readonly CreateEmailCampaignService $createEmailCampaignService,
    ) {
    }

    /**
     * @throws UnsupportedSendOptionException
     */
    #[Route('/email-campaign/create', name: 'api_email_campaign_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEmailCampaignDTO $requestDTO,
    ): Response {
        $this->createEmailCampaignService->createCampaign($requestDTO);

        return $this->createSuccessResponse([]);
    }
}

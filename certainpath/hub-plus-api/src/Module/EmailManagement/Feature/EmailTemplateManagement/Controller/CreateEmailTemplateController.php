<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\CreateUpdateEmailTemplateDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\CreateEmailTemplateService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter\EmailTemplateVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEmailTemplateController extends ApiController
{
    public function __construct(
        private readonly CreateEmailTemplateService $createEmailTemplateService,
    ) {
    }

    #[Route('/email-template/create', name: 'api_email_template_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEmailTemplateDTO $createEmailTemplateDTO,
    ): Response {
        $this->denyAccessUnlessGranted(EmailTemplateVoter::CREATE);

        $emailTemplate = $this->createEmailTemplateService->createEmailTemplate(
            $createEmailTemplateDTO
        );

        return $this->createSuccessResponse($emailTemplate);
    }
}

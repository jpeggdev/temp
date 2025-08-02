<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\GetEmailTemplateService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter\EmailTemplateVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailTemplateController extends ApiController
{
    public function __construct(
        private readonly GetEmailTemplateService $getEmailTemplateService,
    ) {
    }

    #[Route('/email-template/{id}', name: 'api_email_template_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EmailTemplateVoter::READ);

        $emailTemplateData = $this->getEmailTemplateService->getEmailTemplate($id);

        return $this->createSuccessResponse($emailTemplateData);
    }
}

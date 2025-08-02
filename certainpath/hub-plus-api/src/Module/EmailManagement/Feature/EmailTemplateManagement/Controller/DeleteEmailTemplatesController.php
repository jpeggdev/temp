<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\DeleteEmailTemplateService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter\EmailTemplateVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEmailTemplatesController extends ApiController
{
    public function __construct(
        private readonly DeleteEmailTemplateService $duplicateEmailTemplateService,
    ) {
    }

    #[Route('/email-templates/{id}', name: 'api_email_template_delete', methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EmailTemplateVoter::DELETE);

        $this->duplicateEmailTemplateService->deleteEmailTemplate($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Email Template %d has been deleted.', $id),
        ]);
    }
}

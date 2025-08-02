<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\DuplicateEmailTemplateService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter\EmailTemplateVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DuplicateEmailTemplatesController extends ApiController
{
    public function __construct(
        private readonly DuplicateEmailTemplateService $duplicateEmailTemplateService,
    ) {
    }

    #[Route('/email-templates/{id}/duplicate', name: 'api_email_template_duplicate_get', methods: ['POST'])]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EmailTemplateVoter::DUPLICATE);

        $this->duplicateEmailTemplateService->duplicateEmailTemplate($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Email Template %d has been duplicated.', $id),
        ]);
    }
}

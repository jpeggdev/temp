<?php

declare(strict_types=1);

namespace App\Module\EmailManagement\Feature\EmailTemplateManagement\Controller;

use App\Controller\ApiController;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\DTO\Request\CreateUpdateEmailTemplateDTO;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Service\UpdateEmailTemplateService;
use App\Module\EmailManagement\Feature\EmailTemplateManagement\Voter\EmailTemplateVoter;
use App\Repository\EmailTemplateRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmailTemplatesController extends ApiController
{
    public function __construct(
        private readonly EmailTemplateRepository $emailTemplateRepository,
        private readonly UpdateEmailTemplateService $updateEmailTemplateService,
    ) {
    }

    /**
     * @throws \Exception
     */
    #[Route('/email-template/{id}/update', name: 'api_email_template_update', methods: ['PUT', 'PATCH'])]
    public function __invoke(
        int $id,
        #[MapRequestPayload] CreateUpdateEmailTemplateDTO $updateEmailTemplateDTO,
    ): Response {
        $this->denyAccessUnlessGranted(EmailTemplateVoter::UPDATE);

        $emailTemplate = $this->emailTemplateRepository->findOneByIdOrFail($id);

        $this->updateEmailTemplateService->updateEmailTemplate(
            $emailTemplate,
            $updateEmailTemplateDTO
        );

        return $this->createSuccessResponse([
            'message' => sprintf('Email Template %d has been updated.', $id),
        ]);
    }
}

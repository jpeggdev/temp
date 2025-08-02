<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateCategory;

use App\Controller\ApiController;
use App\DTO\Request\CreateUpdateEmailTemplateCategoryDTO;
use App\Entity\EmailTemplateCategory;
use App\Security\Voter\EmailTemplateCategorySecurityVoter;
use App\Service\EmailTemplateCategory\UpdateEmailTemplateCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEmailTemplateCategoryController extends ApiController
{
    public function __construct(
        private readonly UpdateEmailTemplateCategoryService $updateEmailTemplateCategoryService,
    ) {
    }

    #[Route('/email-template-category/{id}', name: 'api_email_template_category_update', methods: ['PUT'])]
    public function __invoke(
        EmailTemplateCategory $emailTemplateCategory,
        #[MapRequestPayload] CreateUpdateEmailTemplateCategoryDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(
            EmailTemplateCategorySecurityVoter::EMAIL_TEMPLATE_CATEGORY_MANAGE,
            $emailTemplateCategory
        );
        $updatedCategory = $this->updateEmailTemplateCategoryService->updateCategory($emailTemplateCategory, $dto);

        return $this->createSuccessResponse($updatedCategory);
    }
}

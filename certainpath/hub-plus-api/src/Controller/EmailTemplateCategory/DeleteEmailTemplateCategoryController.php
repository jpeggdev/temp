<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateCategory;

use App\Controller\ApiController;
use App\Entity\EmailTemplateCategory;
use App\Security\Voter\EmailTemplateCategorySecurityVoter;
use App\Service\EmailTemplateCategory\DeleteEmailTemplateCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEmailTemplateCategoryController extends ApiController
{
    public function __construct(
        private readonly DeleteEmailTemplateCategoryService $deleteEmailTemplateCategoryService,
    ) {
    }

    #[Route('/email-template-category/{id}/delete', name: 'api_email_template_category_delete', methods: ['DELETE'])]
    public function __invoke(EmailTemplateCategory $emailTemplateCategory): Response
    {
        $this->denyAccessUnlessGranted(
            EmailTemplateCategorySecurityVoter::EMAIL_TEMPLATE_CATEGORY_MANAGE,
            $emailTemplateCategory
        );
        $this->deleteEmailTemplateCategoryService->deleteCategory($emailTemplateCategory);

        return $this->createSuccessResponse(['message' => 'Email Template Category deleted successfully.']);
    }
}

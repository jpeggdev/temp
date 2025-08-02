<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateCategory;

use App\Controller\ApiController;
use App\Entity\EmailTemplateCategory;
use App\Security\Voter\EmailTemplateCategorySecurityVoter;
use App\Service\EmailTemplateCategory\GetEditEmailTemplateCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditEmailTemplateCategoryController extends ApiController
{
    public function __construct(
        private readonly GetEditEmailTemplateCategoryService $getEditEmailTemplateCategoryService,
    ) {
    }

    #[Route('/email-template-category/{id}', name: 'api_email_template_category_edit_details', methods: ['GET'])]
    public function __invoke(EmailTemplateCategory $emailTemplateCategory): Response
    {
        $this->denyAccessUnlessGranted(
            EmailTemplateCategorySecurityVoter::EMAIL_TEMPLATE_CATEGORY_MANAGE,
            $emailTemplateCategory
        );
        $categoryDetails = $this->getEditEmailTemplateCategoryService
            ->getEditEmailTemplateCategoryDetails($emailTemplateCategory);

        return $this->createSuccessResponse($categoryDetails);
    }
}

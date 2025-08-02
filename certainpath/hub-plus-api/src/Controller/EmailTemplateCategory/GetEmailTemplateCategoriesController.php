<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateCategory;

use App\Controller\ApiController;
use App\DTO\Request\GetEmailTemplateCategoriesDTO;
use App\Security\Voter\EmailTemplateCategorySecurityVoter;
use App\Service\EmailTemplateCategory\GetEmailTemplateCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEmailTemplateCategoriesController extends ApiController
{
    public function __construct(
        private readonly GetEmailTemplateCategoryService $getEmailTemplateCategoryService,
    ) {
    }

    #[Route('/email-template-categories', name: 'api_email_template_categories_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEmailTemplateCategoriesDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(EmailTemplateCategorySecurityVoter::EMAIL_TEMPLATE_CATEGORY_MANAGE);
        $emailTemplatesCategoriesData = $this->getEmailTemplateCategoryService->getEmailTemplateCategories($dto);

        return $this->createSuccessResponse(
            $emailTemplatesCategoriesData['emailTemplateCategories'],
            $emailTemplatesCategoriesData['totalCount']
        );
    }
}

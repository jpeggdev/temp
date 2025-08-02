<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateCategory;

use App\Controller\ApiController;
use App\DTO\Request\CreateUpdateEmailTemplateCategoryDTO;
use App\Security\Voter\EmailTemplateCategorySecurityVoter;
use App\Service\EmailTemplateCategory\CreateEmailTemplateCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEmailTemplateCategoryController extends ApiController
{
    public function __construct(
        private readonly CreateEmailTemplateCategoryService $createEmailTemplateCategoryService,
    ) {
    }

    #[Route('/email-template-category/create', name: 'api_email_template_category_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEmailTemplateCategoryDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(EmailTemplateCategorySecurityVoter::EMAIL_TEMPLATE_CATEGORY_MANAGE);
        $categoryResponse = $this->createEmailTemplateCategoryService->createCategory($dto);

        return $this->createSuccessResponse($categoryResponse);
    }
}

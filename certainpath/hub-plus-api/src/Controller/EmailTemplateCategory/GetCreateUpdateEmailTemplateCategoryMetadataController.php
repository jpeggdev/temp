<?php

declare(strict_types=1);

namespace App\Controller\EmailTemplateCategory;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Service\EmailTemplateCategory\GetCreateUpdateEmailTemplateCategoryMetadataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetCreateUpdateEmailTemplateCategoryMetadataController extends ApiController
{
    public function __construct(
        private readonly GetCreateUpdateEmailTemplateCategoryMetadataService $getCreateUpdateEmailTemplateCategoryMetadataService,
    ) {
    }

    #[Route(
        '/email-template-category/create-update-metadata',
        name: 'api_email_template_category_get_create_update_metadata',
        methods: ['GET']
    )]
    public function __invoke(LoggedInUserDTO $loggedInUserDTO): Response
    {
        $metadataDto = $this->getCreateUpdateEmailTemplateCategoryMetadataService->getMetadata();

        return $this->createSuccessResponse($metadataDto);
    }
}

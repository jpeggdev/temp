<?php

declare(strict_types=1);

namespace App\Controller\ResourceCategory;

use App\Controller\ApiController;
use App\Entity\ResourceCategory;
use App\Security\Voter\ResourceCategorySecurityVoter;
use App\Service\ResourceCategory\DeleteResourceCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteResourceCategoryController extends ApiController
{
    public function __construct(
        private readonly DeleteResourceCategoryService $deleteResourceCategoryService,
    ) {
    }

    #[Route('/resource/category/{id}/delete', name: 'api_resource_category_delete', methods: ['DELETE'])]
    public function __invoke(ResourceCategory $resourceCategory): Response
    {
        $this->denyAccessUnlessGranted(ResourceCategorySecurityVoter::MANAGE, $resourceCategory);
        $this->deleteResourceCategoryService->deleteCategory($resourceCategory);

        return $this->createSuccessResponse(['message' => 'Category deleted successfully.']);
    }
}

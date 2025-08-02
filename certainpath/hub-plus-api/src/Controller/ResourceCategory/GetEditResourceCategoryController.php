<?php

declare(strict_types=1);

namespace App\Controller\ResourceCategory;

use App\Controller\ApiController;
use App\Entity\ResourceCategory;
use App\Security\Voter\ResourceCategorySecurityVoter;
use App\Service\ResourceCategory\GetEditResourceCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEditResourceCategoryController extends ApiController
{
    public function __construct(
        private readonly GetEditResourceCategoryService $getEditResourceCategoryService,
    ) {
    }

    #[Route('/resource/category/{id}', name: 'api_resource_category_edit_details', methods: ['GET'])]
    public function __invoke(ResourceCategory $resourceCategory): Response
    {
        $this->denyAccessUnlessGranted(ResourceCategorySecurityVoter::MANAGE, $resourceCategory);
        $categoryDetails = $this->getEditResourceCategoryService->getEditResourceCategoryDetails($resourceCategory);

        return $this->createSuccessResponse($categoryDetails);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\ResourceCategory;

use App\Controller\ApiController;
use App\DTO\Request\ResourceCategory\CreateUpdateResourceCategoryDTO;
use App\Security\Voter\ResourceCategorySecurityVoter;
use App\Service\ResourceCategory\CreateResourceCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateResourceCategoryController extends ApiController
{
    public function __construct(
        private readonly CreateResourceCategoryService $createResourceCategoryService,
    ) {
    }

    #[Route('/resource/category/create', name: 'api_resource_category_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateResourceCategoryDTO $dto,
    ): Response {
        $this->denyAccessUnlessGranted(ResourceCategorySecurityVoter::MANAGE);
        $categoryResponse = $this->createResourceCategoryService->createCategory($dto);

        return $this->createSuccessResponse($categoryResponse);
    }
}

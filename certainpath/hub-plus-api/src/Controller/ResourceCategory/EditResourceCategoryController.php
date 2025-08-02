<?php

declare(strict_types=1);

namespace App\Controller\ResourceCategory;

use App\Controller\ApiController;
use App\DTO\Request\ResourceCategory\CreateUpdateResourceCategoryDTO;
use App\Entity\ResourceCategory;
use App\Service\ResourceCategory\EditResourceCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditResourceCategoryController extends ApiController
{
    public function __construct(
        private readonly EditResourceCategoryService $editResourceCategoryService,
    ) {
    }

    #[Route('/resource/category/{id}/edit', name: 'api_resource_category_edit', methods: ['PUT', 'PATCH'])]
    public function __invoke(
        ResourceCategory $resourceCategory,
        #[MapRequestPayload] CreateUpdateResourceCategoryDTO $dto,
    ): Response {
        $responseDTO = $this->editResourceCategoryService->editCategory($resourceCategory, $dto);

        return $this->createSuccessResponse($responseDTO);
    }
}

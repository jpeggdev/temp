<?php

declare(strict_types=1);

namespace App\Controller\ResourceCategory;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\ResourceCategory\GetResourceCategoriesRequestDTO;
use App\Service\ResourceCategory\GetResourceCategoriesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourceCategoriesController extends ApiController
{
    public function __construct(
        private readonly GetResourceCategoriesService $getResourceCategoriesService,
    ) {
    }

    #[Route('/resource/categories', name: 'api_resource_categories_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetResourceCategoriesRequestDTO $requestDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $data = $this->getResourceCategoriesService->getCategories($requestDto);

        return $this->createSuccessResponse(
            $data,
            $data['totalCount']
        );
    }
}

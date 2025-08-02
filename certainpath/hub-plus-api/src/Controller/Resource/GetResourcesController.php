<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\Request\Resource\GetResourcesRequestDTO;
use App\Service\Resource\GetResourcesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourcesController extends ApiController
{
    // region Declarations
    public function __construct(
        private readonly GetResourcesService $getResourcesService,
    ) {
    }
    // endregion

    // region __invoke
    #[Route('/resources', name: 'api_resources_get', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetResourcesRequestDTO $queryDto,
        Request $request,
    ): Response {
        $resourcesData = $this->getResourcesService->getResources($queryDto);

        return $this->createSuccessResponse(
            $resourcesData['resources'],
            $resourcesData['totalCount']
        );
    }
    // endregion
}

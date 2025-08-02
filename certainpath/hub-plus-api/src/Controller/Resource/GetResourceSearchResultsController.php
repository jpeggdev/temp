<?php

declare(strict_types=1);

namespace App\Controller\Resource;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Resource\GetResourceSearchResultsQueryDTO;
use App\Service\Resource\GetResourceSearchResultsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetResourceSearchResultsController extends ApiController
{
    public function __construct(
        private readonly GetResourceSearchResultsService $searchResultsService,
    ) {
    }

    #[Route('/resources/search', name: 'api_resources_search', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetResourceSearchResultsQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $resourcesData = $this->searchResultsService->getResources(
            $queryDto,
            $loggedInUserDTO->getActiveEmployee()
        );

        return $this->createSuccessResponse(
            $resourcesData['data'],
            $resourcesData['totalCount']
        );
    }
}

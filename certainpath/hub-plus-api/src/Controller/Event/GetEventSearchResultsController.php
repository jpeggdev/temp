<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\Event\GetEventSearchResultsQueryDTO;
use App\Service\Event\GetEventSearchResultsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventSearchResultsController extends ApiController
{
    public function __construct(
        private readonly GetEventSearchResultsService $searchResultsService,
    ) {
    }

    #[Route('/events/search', name: 'api_events_search', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] GetEventSearchResultsQueryDTO $queryDto,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): Response {
        $eventData = $this->searchResultsService->getEvents($queryDto, $loggedInUserDTO->getActiveEmployee());

        return $this->createSuccessResponse(
            $eventData['data'],
            $eventData['totalCount']
        );
    }
}

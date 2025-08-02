<?php

declare(strict_types=1);

namespace App\Controller\EventCategory;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventCategory\EventCategoryQueryDTO;
use App\Service\EventCategory\EventCategoryQueryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventCategoriesController extends ApiController
{
    public function __construct(
        private readonly EventCategoryQueryService $eventCategoryQueryService,
    ) {
    }

    #[Route('/event-categories', name: 'api_event_categories_get', methods: ['GET'])]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
        #[MapQueryString] EventCategoryQueryDTO $queryDto = new EventCategoryQueryDTO(),
    ): JsonResponse {
        $eventCategoriesResponse = $this->eventCategoryQueryService->getEventCategories($queryDto);

        return $this->createSuccessResponse(
            $eventCategoriesResponse['eventCategories'],
            $eventCategoriesResponse['totalCount']
        );
    }
}

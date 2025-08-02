<?php

declare(strict_types=1);

namespace App\Controller\EventCategory;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventCategory\CreateEventCategoryDTO;
use App\Service\EventCategory\CreateEventCategoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventCategoryController extends ApiController
{
    public function __construct(
        private readonly CreateEventCategoryService $createEventCategoryService,
    ) {
    }

    #[Route('/event-categories/create', name: 'api_event_category_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateEventCategoryDTO $createEventCategoryDTO,
        LoggedInUserDTO $loggedInUserDTO,
        Request $request,
    ): JsonResponse {
        $eventCategory = $this->createEventCategoryService
            ->createEventCategory($createEventCategoryDTO, $loggedInUserDTO);

        return $this->createSuccessResponse([
            'eventCategory' => $eventCategory,
        ]);
    }
}

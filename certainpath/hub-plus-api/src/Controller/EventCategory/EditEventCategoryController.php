<?php

declare(strict_types=1);

namespace App\Controller\EventCategory;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\DTO\Request\EventCategory\EditEventCategoryDTO;
use App\Entity\EventCategory;
use App\Service\EventCategory\EditEventCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class EditEventCategoryController extends ApiController
{
    public function __construct(private readonly EditEventCategoryService $editEventCategoryService)
    {
    }

    #[Route('/event-categories/{id}/edit', name: 'api_event_category_edit', methods: ['PUT'])]
    public function __invoke(
        EventCategory $eventCategory,
        #[MapRequestPayload] EditEventCategoryDTO $editEventCategoryDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $updatedEventCategory = $this->editEventCategoryService->editEventCategory(
            $eventCategory,
            $editEventCategoryDTO,
            $loggedInUserDTO
        );

        return $this->createSuccessResponse($updatedEventCategory);
    }
}

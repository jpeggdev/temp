<?php

declare(strict_types=1);

namespace App\Controller\EventCategory;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventCategory;
use App\Service\EventCategory\DeleteEventCategoryService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventCategoryController extends ApiController
{
    public function __construct(
        private readonly DeleteEventCategoryService $deleteEventCategoryService,
    ) {
    }

    #[Route('/event-categories/{id}', name: 'api_event_category_delete', methods: ['DELETE'])]
    public function __invoke(EventCategory $eventCategory, LoggedInUserDTO $loggedInUserDTO): Response
    {
        $result = $this->deleteEventCategoryService->deleteEventCategory($eventCategory, $loggedInUserDTO);

        return $this->createSuccessResponse($result);
    }
}

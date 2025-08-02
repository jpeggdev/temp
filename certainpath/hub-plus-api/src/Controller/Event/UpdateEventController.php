<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\Request\Event\CreateUpdateEventDTO;
use App\Entity\Event;
use App\Service\Event\UpdateEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventController extends ApiController
{
    public function __construct(
        private readonly UpdateEventService $updateEventService,
    ) {
    }

    #[Route('/event/{id}/update', name: 'api_event_update', methods: ['PUT', 'PATCH'])]
    public function __invoke(
        Event $event,
        #[MapRequestPayload] CreateUpdateEventDTO $editEventDTO,
    ): Response {
        $eventResponse = $this->updateEventService->updateEvent(
            $event,
            $editEventDTO
        );

        return $this->createSuccessResponse($eventResponse);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\DTO\Request\Event\CreateUpdateEventDTO;
use App\Service\Event\CreateEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventController extends ApiController
{
    public function __construct(
        private readonly CreateEventService $createEventService,
    ) {
    }

    #[Route('/event/create', name: 'api_event_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEventDTO $createEventDTO,
    ): Response {
        $eventResponse = $this->createEventService->createEvent($createEventDTO);

        return $this->createSuccessResponse($eventResponse);
    }
}

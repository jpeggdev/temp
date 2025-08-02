<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\Entity\Event;
use App\Service\Event\DeleteEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventController extends ApiController
{
    public function __construct(
        private readonly DeleteEventService $deleteEventService,
    ) {
    }

    #[Route('/event/{id}/delete', name: 'api_event_delete', methods: ['DELETE'])]
    public function __invoke(Event $event): Response
    {
        $responseDTO = $this->deleteEventService->deleteEvent($event);

        return $this->createSuccessResponse($responseDTO);
    }
}

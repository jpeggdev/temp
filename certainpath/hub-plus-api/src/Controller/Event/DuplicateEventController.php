<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\Entity\Event;
use App\Service\Event\DuplicateEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DuplicateEventController extends ApiController
{
    public function __construct(
        private readonly DuplicateEventService $duplicateEventService,
    ) {
    }

    #[Route('/event/{id}/duplicate', name: 'api_event_duplicate', methods: ['POST'])]
    public function __invoke(
        Event $originalEvent,
    ): Response {
        $responseDTO = $this->duplicateEventService->duplicateEvent($originalEvent);

        return $this->createSuccessResponse($responseDTO);
    }
}

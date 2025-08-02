<?php

declare(strict_types=1);

namespace App\Controller\EventType;

use App\Controller\ApiController;
use App\Entity\EventType;
use App\Service\EventType\DeleteEventTypeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventTypeController extends ApiController
{
    public function __construct(
        private readonly DeleteEventTypeService $deleteEventTypeService,
    ) {
    }

    #[Route(
        '/event/type/{id}/delete',
        name: 'api_event_type_delete',
        methods: ['DELETE']
    )]
    public function __invoke(EventType $eventType): Response
    {
        $this->deleteEventTypeService->deleteEventType($eventType);

        return $this->createSuccessResponse([
            'message' => 'Event type deleted successfully.',
        ]);
    }
}

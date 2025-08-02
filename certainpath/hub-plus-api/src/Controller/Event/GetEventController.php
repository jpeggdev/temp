<?php

declare(strict_types=1);

namespace App\Controller\Event;

use App\Controller\ApiController;
use App\Entity\Event;
use App\Service\Event\GetEventService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventController extends ApiController
{
    public function __construct(
        private readonly GetEventService $getEventService,
    ) {
    }

    #[Route(
        '/event/{uuid}',
        name: 'api_event_get',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(Event $event): Response
    {
        $eventData = $this->getEventService->getEvent($event);

        return $this->createSuccessResponse($eventData);
    }
}

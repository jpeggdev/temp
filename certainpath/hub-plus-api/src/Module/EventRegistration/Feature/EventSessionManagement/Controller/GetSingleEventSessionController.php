<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\GetSingleEventSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetSingleEventSessionController extends ApiController
{
    public function __construct(
        private readonly GetSingleEventSessionService $service,
    ) {
    }

    /**
     * Retrieves a single event session by its UUID.
     */
    #[Route(
        '/event-sessions/{uuid}',
        name: 'api_event_session_get',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(EventSession $eventSession): Response
    {
        $sessionDTO = $this->service->getSession($eventSession);

        return $this->createSuccessResponse($sessionDTO);
    }
}

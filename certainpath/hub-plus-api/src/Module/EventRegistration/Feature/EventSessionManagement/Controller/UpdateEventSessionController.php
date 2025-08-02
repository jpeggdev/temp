<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\CreateUpdateEventSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\UpdateEventSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventSessionController extends ApiController
{
    public function __construct(
        private readonly UpdateEventSessionService $updateEventSessionService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}',
        name: 'api_event_sessions_update',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['PUT'],
    )]
    public function __invoke(
        EventSession $eventSession,
        #[MapRequestPayload] CreateUpdateEventSessionRequestDTO $dto,
    ): Response {
        $sessionResponse = $this->updateEventSessionService->updateSession($eventSession, $dto);

        return $this->createSuccessResponse($sessionResponse);
    }
}

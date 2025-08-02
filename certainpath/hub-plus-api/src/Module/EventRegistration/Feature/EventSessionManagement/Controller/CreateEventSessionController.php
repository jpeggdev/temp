<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\CreateUpdateEventSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\CreateEventSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventSessionController extends ApiController
{
    public function __construct(
        private readonly CreateEventSessionService $createEventSessionService,
    ) {
    }

    #[Route('/event-sessions', name: 'api_event_sessions_create', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CreateUpdateEventSessionRequestDTO $dto,
    ): Response {
        $sessionResponse = $this->createEventSessionService->createSession($dto);

        return $this->createSuccessResponse($sessionResponse);
    }
}

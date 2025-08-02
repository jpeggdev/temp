<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\DeleteEventSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventSessionController extends ApiController
{
    public function __construct(private readonly DeleteEventSessionService $service)
    {
    }

    #[Route(
        '/event-sessions/{uuid}/delete',
        name: 'api_event_session_delete',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['DELETE'],
    )]
    public function __invoke(EventSession $eventSession): Response
    {
        $responseDTO = $this->service->delete($eventSession);

        return $this->createSuccessResponse($responseDTO);
    }
}

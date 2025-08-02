<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request\SetPublishedEventSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventSessionManagement\Service\SetPublishedEventSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private/event-sessions')]
class SetPublishedEventSessionController extends ApiController
{
    public function __construct(
        private readonly SetPublishedEventSessionService $service,
    ) {
    }

    #[Route(
        '/{uuid}/published',
        name: 'api_event_sessions_set_published',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['PATCH']
    )]
    public function __invoke(
        EventSession $eventSession,
        #[MapRequestPayload] SetPublishedEventSessionRequestDTO $dto,
    ): Response {
        $responseDTO = $this->service->setPublished($eventSession, $dto->isPublished);

        return $this->createSuccessResponse($responseDTO);
    }
}

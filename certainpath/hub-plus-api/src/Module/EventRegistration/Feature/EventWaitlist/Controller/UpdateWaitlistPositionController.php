<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\UpdateWaitlistPositionRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\UpdateWaitlistPositionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class UpdateWaitlistPositionController extends ApiController
{
    public function __construct(
        private readonly UpdateWaitlistPositionService $updateWaitlistPositionService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/waitlist/update-position',
        name: 'api_event_session_waitlist_update_position',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapRequestPayload] UpdateWaitlistPositionRequestDTO $requestDTO,
    ): Response {
        $this->updateWaitlistPositionService->updatePosition($eventSession, $requestDTO);

        return $this->createSuccessResponse([
            'message' => 'Waitlist position updated successfully',
        ]);
    }
}

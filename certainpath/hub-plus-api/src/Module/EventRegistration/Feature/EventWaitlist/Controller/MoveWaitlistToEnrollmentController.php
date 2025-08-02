<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\MoveWaitlistToEnrollmentRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\MoveWaitlistToEnrollmentService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class MoveWaitlistToEnrollmentController extends ApiController
{
    public function __construct(
        private readonly MoveWaitlistToEnrollmentService $service,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/waitlist/register',
        name: 'api_event_session_waitlist_register',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapRequestPayload]
        MoveWaitlistToEnrollmentRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->service->promoteItem($eventSession, $requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}

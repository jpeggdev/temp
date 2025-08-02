<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\MoveEnrollmentToWaitlistRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\MoveEnrollmentToWaitlistService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class MoveEnrollmentToWaitlistController extends ApiController
{
    public function __construct(
        private readonly MoveEnrollmentToWaitlistService $service,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/waitlist/from-enrollment',
        name: 'api_event_session_enrollment_to_waitlist',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapRequestPayload]
        MoveEnrollmentToWaitlistRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->service->demoteItem($eventSession, $requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}

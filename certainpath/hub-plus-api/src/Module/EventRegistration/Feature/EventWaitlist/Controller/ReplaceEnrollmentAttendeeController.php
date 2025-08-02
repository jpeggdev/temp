<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\ReplaceEnrollmentAttendeeRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\ReplaceEnrollmentAttendeeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class ReplaceEnrollmentAttendeeController extends ApiController
{
    public function __construct(
        private readonly ReplaceEnrollmentAttendeeService $replaceService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/enrollments/replace-attendee',
        name: 'api_event_session_enrollment_replace_attendee',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapRequestPayload] ReplaceEnrollmentAttendeeRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->replaceService->replaceAttendee($eventSession, $requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\ReplaceEnrollmentWithEmployeeRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\ReplaceEnrollmentWithEmployeeService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class ReplaceEnrollmentWithEmployeeController extends ApiController
{
    public function __construct(
        private readonly ReplaceEnrollmentWithEmployeeService $replaceService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/enrollments/replace-with-employee',
        name: 'api_event_session_enrollment_replace_with_employee',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapRequestPayload] ReplaceEnrollmentWithEmployeeRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->replaceService->replaceAttendeeWithEmployee($eventSession, $requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}

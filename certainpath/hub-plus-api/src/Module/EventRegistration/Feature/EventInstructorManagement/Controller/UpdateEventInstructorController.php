<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\UpdateEventInstructorRequestDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Service\UpdateEventInstructorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventInstructorController extends ApiController
{
    public function __construct(
        private readonly UpdateEventInstructorService $updateEventInstructorService,
    ) {
    }

    #[Route(
        '/event-instructors/{id}/update',
        name: 'api_event_instructor_update_alternative',
        methods: ['PUT', 'PATCH']
    )]
    public function __invoke(
        EventInstructor $eventInstructor,
        #[MapRequestPayload] UpdateEventInstructorRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->updateEventInstructorService->updateInstructor($eventInstructor, $requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Service\DeleteEventInstructorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventInstructorController extends ApiController
{
    public function __construct(
        private readonly DeleteEventInstructorService $deleteEventInstructorService,
    ) {
    }

    #[Route(
        '/event-instructors/{id}/delete',
        name: 'api_event_instructor_delete_alternative',
        methods: ['DELETE']
    )]
    public function __invoke(EventInstructor $eventInstructor): Response
    {
        $responseDTO = $this->deleteEventInstructorService->deleteInstructor($eventInstructor);

        return $this->createSuccessResponse($responseDTO);
    }
}

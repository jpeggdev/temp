<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Controller;

use App\Controller\ApiController;
use App\Entity\EventInstructor;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Service\GetEventInstructorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class GetEventInstructorController extends ApiController
{
    public function __construct(
        private readonly GetEventInstructorService $getEventInstructorService,
    ) {
    }

    #[Route(
        '/event-instructors/{id}',
        name: 'api_event_instructor_get_single',
        methods: ['GET']
    )]
    public function __invoke(EventInstructor $eventInstructor): Response
    {
        $responseDTO = $this->getEventInstructorService->getInstructor($eventInstructor);

        return $this->createSuccessResponse($responseDTO);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request\CreateEventInstructorRequestDTO;
use App\Module\EventRegistration\Feature\EventInstructorManagement\Service\CreateEventInstructorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventInstructorController extends ApiController
{
    public function __construct(
        private readonly CreateEventInstructorService $createEventInstructorService,
    ) {
    }

    #[Route(
        '/event-instructors/create',
        name: 'api_event_instructor_create_alternative',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] CreateEventInstructorRequestDTO $requestDTO,
    ): Response {
        $responseDTO = $this->createEventInstructorService->createInstructor($requestDTO);

        return $this->createSuccessResponse($responseDTO);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\EventEnrollmentsQueryDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Service\GetEventEnrollmentsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class GetEventEnrollmentsController extends ApiController
{
    public function __construct(
        private readonly GetEventEnrollmentsService $getEventEnrollmentsService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/enrollments',
        name: 'api_event_session_enrollments',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(
        LoggedInUserDTO $loggedInUserDTO,
        EventSession $eventSession,
        #[MapQueryString] EventEnrollmentsQueryDTO $queryDto = new EventEnrollmentsQueryDTO(),
    ): JsonResponse {
        $result = $this->getEventEnrollmentsService->getEventEnrollments($eventSession, $queryDto);

        return $this->createSuccessResponse(
            $result['items'],
            $result['totalCount']
        );
    }
}

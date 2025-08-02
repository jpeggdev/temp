<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\Service\GetInProgressEventCheckoutSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetInProgressEventCheckoutSessionController extends ApiController
{
    public function __construct(
        private readonly GetInProgressEventCheckoutSessionService $getInProgressEventCheckoutSessionService,
    ) {
    }

    #[Route(
        '/event-sessions/{uuid}/checkout/in-progress',
        name: 'api_event_checkout_session_in_progress',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(
        EventSession $eventSession,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $inProgressDto = $this->getInProgressEventCheckoutSessionService->getInProgressSession(
            $eventSession,
            $loggedInUserDTO->getActiveCompany(),
            $loggedInUserDTO->getActiveEmployee()
        );

        return $this->createSuccessResponse($inProgressDto);
    }
}

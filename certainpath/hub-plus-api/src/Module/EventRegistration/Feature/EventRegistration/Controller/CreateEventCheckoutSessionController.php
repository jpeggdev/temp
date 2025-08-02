<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\CreateEventCheckoutSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\CreateEventCheckoutSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class CreateEventCheckoutSessionController extends ApiController
{
    public function __construct(
        private readonly CreateEventCheckoutSessionService $createEventCheckoutSessionService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/create',
        name: 'api_event_checkout_session_create_alternative',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] CreateEventCheckoutSessionRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $sessionResponse = $this->createEventCheckoutSessionService->createSession(
            $requestDTO,
            $loggedInUserDTO->getActiveCompany(),
            $loggedInUserDTO->getActiveEmployee()
        );

        return $this->createSuccessResponse($sessionResponse);
    }
}

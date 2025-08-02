<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ResetEventCheckoutSessionReservationExpirationRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\ResetEventCheckoutSessionReservationExpirationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class ResetEventCheckoutSessionReservationExpirationController extends ApiController
{
    public function __construct(
        private readonly ResetEventCheckoutSessionReservationExpirationService $resetService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/reset-reservation-expiration',
        name: 'api_event_checkout_session_reset_reservation_expiration',
        methods: ['POST']
    )]
    public function __invoke(
        #[MapRequestPayload] ResetEventCheckoutSessionReservationExpirationRequestDTO $requestDto,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $updatedDto = $this->resetService->resetReservationExpiration(
            $requestDto->eventCheckoutSessionUuid,
            $loggedInUserDTO->getActiveEmployee()
        );

        return $this->createSuccessResponse($updatedDto);
    }
}

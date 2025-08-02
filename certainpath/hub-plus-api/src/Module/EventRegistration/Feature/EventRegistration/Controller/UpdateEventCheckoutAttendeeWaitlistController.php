<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\UpdateEventCheckoutAttendeeWaitlistRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\UpdateEventCheckoutAttendeeWaitlistService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventCheckoutAttendeeWaitlistController extends ApiController
{
    public function __construct(
        private readonly UpdateEventCheckoutAttendeeWaitlistService $updateWaitlistService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/{uuid}/attendees/waitlist',
        name: 'api_event_checkout_attendees_waitlist_update',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['POST']
    )]
    public function __invoke(
        EventCheckout $eventCheckoutSession,
        #[MapRequestPayload] UpdateEventCheckoutAttendeeWaitlistRequestDTO $requestDTO,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $response = $this->updateWaitlistService->updateWaitlistStatus(
            $eventCheckoutSession,
            $requestDTO,
            $loggedInUserDTO
        );

        return $this->createSuccessResponse($response);
    }
}

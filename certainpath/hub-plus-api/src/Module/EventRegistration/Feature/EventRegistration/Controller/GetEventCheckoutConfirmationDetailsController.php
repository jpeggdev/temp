<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\Service\GetEventCheckoutConfirmationDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventCheckoutConfirmationDetailsController extends ApiController
{
    public function __construct(
        private readonly GetEventCheckoutConfirmationDetailsService $getEventCheckoutConfirmationDetailsService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/{uuid}/confirmation-details',
        name: 'api_event_checkout_sessions_confirmation_details',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(
        EventCheckout $eventCheckoutSession,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $detailsDto = $this->getEventCheckoutConfirmationDetailsService->getDetails(
            $eventCheckoutSession,
            $loggedInUserDTO->getActiveCompany()
        );

        return $this->createSuccessResponse($detailsDto);
    }
}

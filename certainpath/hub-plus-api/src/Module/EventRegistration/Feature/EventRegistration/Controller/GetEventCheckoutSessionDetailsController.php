<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\Service\GetEventCheckoutSessionDetailsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventCheckoutSessionDetailsController extends ApiController
{
    public function __construct(
        private readonly GetEventCheckoutSessionDetailsService $getEventCheckoutSessionDetailsService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/{uuid}/details',
        name: 'api_event_checkout_sessions_details',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(
        EventCheckout $eventCheckoutSession,
        LoggedInUserDTO $loggedInUserDTO,
    ): Response {
        $detailsDto = $this->getEventCheckoutSessionDetailsService->getDetails(
            $eventCheckoutSession,
            $loggedInUserDTO->getActiveCompany(),
            $loggedInUserDTO
        );

        return $this->createSuccessResponse($detailsDto);
    }
}

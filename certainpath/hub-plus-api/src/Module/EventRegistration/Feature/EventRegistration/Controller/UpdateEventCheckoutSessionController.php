<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\UpdateEventCheckoutSessionRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\UpdateEventCheckoutSessionService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class UpdateEventCheckoutSessionController extends ApiController
{
    public function __construct(
        private readonly UpdateEventCheckoutSessionService $updateEventCheckoutSessionService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/{uuid}/update',
        name: 'api_event_checkout_session_update',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['PUT', 'PATCH']
    )]
    public function __invoke(
        EventCheckout $eventCheckoutSession,
        #[MapRequestPayload] UpdateEventCheckoutSessionRequestDTO $editSessionDTO,
    ): Response {
        $sessionResponse = $this->updateEventCheckoutSessionService->updateSession(
            $eventCheckoutSession,
            $editSessionDTO
        );

        return $this->createSuccessResponse($sessionResponse);
    }
}

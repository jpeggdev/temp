<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Controller;

use App\Controller\ApiController;
use App\DTO\LoggedInUserDTO;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\Service\GetEventCheckoutConfirmationPdfService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/private')]
class GetEventCheckoutConfirmationPdfController extends ApiController
{
    public function __construct(
        private readonly GetEventCheckoutConfirmationPdfService $pdfService,
    ) {
    }

    #[Route(
        '/event-checkout-sessions/{uuid}/confirmation-download',
        name: 'api_event_checkout_sessions_confirmation_pdf',
        requirements: ['uuid' => '[0-9A-Fa-f]{8}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{4}\-[0-9A-Fa-f]{12}'],
        methods: ['GET']
    )]
    public function __invoke(
        EventCheckout $eventCheckoutSession,
        LoggedInUserDTO $loggedInUserDTO,
    ): StreamedResponse {
        return $this->pdfService->generatePdfDownload($eventCheckoutSession);
    }
}

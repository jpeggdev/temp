<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVenueManagement\Service\GetEventVenueService;
use App\Module\EventRegistration\Feature\EventVenueManagement\Voter\EventVenueVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class GetEventVenueController extends ApiController
{
    public function __construct(
        private readonly GetEventVenueService $getEventVoucherService,
    ) {
    }

    #[Route('/event-venue/{id}', name: 'api_event_venue_get', methods: ['GET'])]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EventVenueVoter::READ);

        $eventVenue = $this->getEventVoucherService->getVenue($id);

        return $this->createSuccessResponse($eventVenue);
    }
}

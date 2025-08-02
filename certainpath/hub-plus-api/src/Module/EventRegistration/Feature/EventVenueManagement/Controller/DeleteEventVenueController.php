<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Controller;

use App\Controller\ApiController;
use App\Module\EventRegistration\Feature\EventVenueManagement\Service\DeleteEventVenueService;
use App\Module\EventRegistration\Feature\EventVenueManagement\Voter\EventVenueVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/private')]
class DeleteEventVenueController extends ApiController
{
    public function __construct(
        private readonly DeleteEventVenueService $deleteEventVenueService,
    ) {
    }

    #[Route('/event-venue/{id}', name: 'api_event_venue_delete', methods: ['DELETE'])]
    public function __invoke(int $id): Response
    {
        $this->denyAccessUnlessGranted(EventVenueVoter::DELETE);

        $deletedEventVenue = $this->deleteEventVenueService->deleteEventVenue($id);

        return $this->createSuccessResponse([
            'message' => sprintf('Event Venue %d has been deleted.', $deletedEventVenue->getName()),
        ]);
    }
}

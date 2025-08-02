<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Service;

use App\Entity\EventVenue;
use App\Repository\EventVenueRepository;

readonly class DeleteEventVenueService
{
    public function __construct(
        private EventVenueRepository $eventVenueRepository,
    ) {
    }

    public function deleteEventVenue(int $id): EventVenue
    {
        $eventVenueToDelete = $this->eventVenueRepository->findOneByIdOrFail($id);

        $this->eventVenueRepository->softDelete($eventVenueToDelete, true);

        return $eventVenueToDelete;
    }
}

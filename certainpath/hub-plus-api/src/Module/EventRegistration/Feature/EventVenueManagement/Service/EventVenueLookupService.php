<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Service;

use App\Entity\EventVenue;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\EventVenueLookupQueryDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Response\EventVenueLookupResponseDTO;
use App\Repository\EventVenueRepository;

readonly class EventVenueLookupService
{
    public function __construct(
        private EventVenueRepository $venueRepository,
    ) {
    }

    public function lookupVenues(EventVenueLookupQueryDTO $dto): array
    {
        $venues = $this->venueRepository->findVenuesByLookup($dto);
        $totalCount = $this->venueRepository->getLookupTotalCount($dto);

        // Convert entities to simple DTOs
        $venueDTOs = array_map(
            static fn (EventVenue $v) => EventVenueLookupResponseDTO::fromEntity($v),
            $venues
        );

        return [
            'venues' => $venueDTOs,
            'totalCount' => $totalCount,
        ];
    }
}

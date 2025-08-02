<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Service;

use App\Entity\EventVenue;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Request\CreateUpdateEventVenueDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Response\EventVenueResponseDTO;
use App\Repository\EventVenueRepository;

readonly class CreateEventVenueService
{
    public function __construct(
        private EventVenueRepository $eventVenueRepository,
    ) {
    }

    public function createVenue(CreateUpdateEventVenueDTO $dto): EventVenueResponseDTO
    {
        $eventVenue = (new EventVenue())
            ->setName($dto->name)
            ->setDescription($dto->description)
            ->setAddress($dto->address)
            ->setAddress2($dto->address2)
            ->setCity($dto->city)
            ->setState($dto->state)
            ->setPostalCode($dto->postalCode)
            ->setCountry($dto->country)
            ->setIsActive(true);

        $this->eventVenueRepository->save($eventVenue, true);

        return EventVenueResponseDTO::fromEntity($eventVenue);
    }
}

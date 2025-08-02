<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Service;

use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Request\CreateUpdateEventVenueDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Response\EventVenueResponseDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\Exception\EventVenueUpdateException;
use App\Repository\EventVenueRepository;

readonly class EditEventVenueService
{
    public function __construct(
        private EventVenueRepository $eventVenueRepository,
    ) {
    }

    public function editVenue(
        int $venueId,
        CreateUpdateEventVenueDTO $dto,
    ): EventVenueResponseDTO {
        $eventVenue = $this->eventVenueRepository->findOneByIdOrFail($venueId);

        if (!$eventVenue->isActive() || $eventVenue->getDeletedAt()) {
            throw new EventVenueUpdateException(message: 'The venue is deleted.');
        }

        $eventVenue
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

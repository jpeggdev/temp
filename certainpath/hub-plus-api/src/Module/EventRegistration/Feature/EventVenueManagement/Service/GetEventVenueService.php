<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVenueManagement\Service;

use App\DTO\Query\PaginationDTO;
use App\Entity\EventVenue;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Query\GetEventVenuesDTO;
use App\Module\EventRegistration\Feature\EventVenueManagement\DTO\Response\EventVenueResponseDTO;
use App\Repository\EventVenueRepository;

readonly class GetEventVenueService
{
    public function __construct(
        private EventVenueRepository $eventVenueRepository,
    ) {
    }

    public function getVenue(int $id): EventVenueResponseDTO
    {
        $eventVenue = $this->eventVenueRepository->findOneByIdOrFail($id);

        return EventVenueResponseDTO::fromEntity($eventVenue);
    }

    public function getVenues(
        GetEventVenuesDTO $queryDto,
        PaginationDTO $paginationDTO,
    ): array {
        $eventVenues = $this->eventVenueRepository->findAllByDTO($queryDto, $paginationDTO);
        $totalCount = $this->eventVenueRepository->getCountByDTO($queryDto);

        $eventVenuesDTOs = array_map(
            static fn (EventVenue $eventVenue) => EventVenueResponseDTO::fromEntity($eventVenue),
            $eventVenues->toArray()
        );

        return [
            'eventVenues' => $eventVenuesDTOs,
            'totalCount' => $totalCount,
        ];
    }
}

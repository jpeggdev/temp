<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Request\Event\EventLookupRequestDTO;
use App\DTO\Response\Event\EventLookupResponseDTO;
use App\Entity\Event;
use App\Repository\EventRepository\EventRepository;

readonly class EventLookupService
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function lookupEvents(EventLookupRequestDTO $dto): array
    {
        $events = $this->eventRepository->findEventsByLookup($dto);
        $totalCount = $this->eventRepository->getLookupTotalCount($dto);

        $eventDtos = array_map(
            static fn (Event $e) => EventLookupResponseDTO::fromEntity($e),
            $events
        );

        return [
            'events' => $eventDtos,
            'totalCount' => $totalCount,
        ];
    }
}

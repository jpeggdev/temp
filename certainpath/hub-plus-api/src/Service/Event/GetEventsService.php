<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Request\Event\GetEventsRequestDTO;
use App\DTO\Response\Event\GetEventsResponseDTO;
use App\Entity\Event;
use App\Repository\EventRepository\EventRepository;

final readonly class GetEventsService
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    /**
     * @return array{
     *     events: GetEventsResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getEvents(GetEventsRequestDTO $queryDto): array
    {
        $events = $this->eventRepository->findEventsByQuery($queryDto);
        $totalCount = $this->eventRepository->getTotalCount($queryDto);

        $eventDtos = array_map(
            fn (Event $event) => GetEventsResponseDTO::fromEntity($event),
            $events
        );

        return [
            'events' => $eventDtos,
            'totalCount' => $totalCount,
        ];
    }
}

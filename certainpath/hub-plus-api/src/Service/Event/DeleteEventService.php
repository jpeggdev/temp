<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Response\Event\DeleteEventResponseDTO;
use App\Entity\Event;
use App\Exception\Event\EventDeleteException;
use App\Repository\EventRepository\EventRepository;

readonly class DeleteEventService
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function deleteEvent(Event $event): DeleteEventResponseDTO
    {
        if ($event->getEventSessions()->count() > 0) {
            throw new EventDeleteException('Cannot delete this event because it has one or more sessions associated with it.');
        }

        $eventId = $event->getId();
        $this->eventRepository->remove($event, true);

        return new DeleteEventResponseDTO(
            id: $eventId,
            message: 'Event deleted successfully.'
        );
    }
}

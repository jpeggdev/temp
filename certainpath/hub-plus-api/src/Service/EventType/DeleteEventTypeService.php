<?php

declare(strict_types=1);

namespace App\Service\EventType;

use App\Entity\EventType;
use App\Exception\EventTypeInUseException;
use App\Repository\EventRepository\EventRepository;
use App\Repository\EventTypeRepository;

readonly class DeleteEventTypeService
{
    public function __construct(
        private EventRepository $eventRepository,
        private EventTypeRepository $eventTypeRepository,
    ) {
    }

    public function deleteEventType(EventType $eventType): void
    {
        $inUseCount = $this->eventRepository->count([
            'eventType' => $eventType,
        ]);

        if ($inUseCount > 0) {
            throw new EventTypeInUseException(sprintf('Cannot delete event type "%s" because %d event(s) are still using it.', $eventType->getName(), $inUseCount));
        }

        $this->eventTypeRepository->remove($eventType, true);
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\DTO\Response\Event\SetPublishedEventResponseDTO;
use App\Entity\Event;
use App\Repository\EventRepository\EventRepository;

readonly class SetPublishedEventService
{
    public function __construct(
        private EventRepository $eventRepository,
    ) {
    }

    public function setPublished(Event $event, bool $isPublished): SetPublishedEventResponseDTO
    {
        $event->setIsPublished($isPublished);
        $this->eventRepository->save($event, flush: true);

        return SetPublishedEventResponseDTO::fromEntity($event);
    }
}

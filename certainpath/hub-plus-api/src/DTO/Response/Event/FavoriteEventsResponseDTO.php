<?php

declare(strict_types=1);

namespace App\DTO\Response\Event;

use App\Entity\Employee;
use App\Entity\Event;

readonly class FavoriteEventsResponseDTO
{
    /**
     * @param FavoriteEventDTO[] $events
     */
    public function __construct(
        public array $events,
    ) {
    }

    /**
     * @param Event[] $events
     */
    public static function fromEntities(array $events, Employee $employee): self
    {
        $eventDTOs = array_map(
            fn (Event $event) => FavoriteEventDTO::fromEntity($event),
            $events
        );

        return new self($eventDTOs);
    }
}

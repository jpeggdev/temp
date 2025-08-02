<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

use App\Entity\EventSession;

class EventSessionLookupResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public int $eventId,
        public ?\DateTimeImmutable $startDate,
    ) {
    }

    public static function fromEntity(EventSession $s): self
    {
        return new self(
            id: $s->getId(),
            uuid: $s->getUuid(),
            eventId: $s->getEvent()?->getId() ?? 0,
            startDate: $s->getStartDate(),
        );
    }
}

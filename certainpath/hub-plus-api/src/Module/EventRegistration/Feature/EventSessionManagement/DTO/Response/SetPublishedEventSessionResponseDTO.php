<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

use App\Entity\EventSession;

class SetPublishedEventSessionResponseDTO
{
    public function __construct(
        public string $uuid,
        public bool $isPublished,
        public int $eventId,
    ) {
    }

    public static function fromEntity(EventSession $s): self
    {
        return new self(
            uuid: $s->getUuid(),
            isPublished: (bool) $s->getIsPublished(),
            eventId: $s->getEvent()?->getId() ?? 0,
        );
    }
}

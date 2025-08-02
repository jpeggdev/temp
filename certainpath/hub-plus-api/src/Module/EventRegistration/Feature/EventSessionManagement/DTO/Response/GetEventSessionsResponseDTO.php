<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

use App\Entity\EventSession;

class GetEventSessionsResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public int $eventId,
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $endDate,
        public int $maxEnrollments,
        public ?string $virtualLink,
        public ?string $notes,
        public bool $isPublished,
        public ?\DateTimeImmutable $createdAt,
        public ?string $timezoneIdentifier,
        public ?string $timezoneShortName,
    ) {
    }

    public static function fromEntity(EventSession $s): self
    {
        return new self(
            id: $s->getId(),
            uuid: $s->getUuid(),
            eventId: $s->getEvent()?->getId() ?? 0,
            startDate: $s->getStartDate(),
            endDate: $s->getEndDate(),
            maxEnrollments: $s->getMaxEnrollments(),
            virtualLink: $s->getVirtualLink(),
            notes: $s->getNotes(),
            isPublished: (bool) $s->getIsPublished(),
            createdAt: $s->getCreatedAt(),
            timezoneIdentifier: $s->getTimezone()?->getIdentifier(),
            timezoneShortName: $s->getTimezone()?->getShortName(),
        );
    }
}

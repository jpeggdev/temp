<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

use App\Entity\EventSession;

class GetSingleEventSessionResponseDTO
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
        public ?string $name,
        public ?\DateTimeImmutable $createdAt,
        public ?int $instructorId,
        public ?string $instructorName,
        public bool $isVirtualOnly,
        public ?int $venueId,
        public ?string $venueName,
        public ?int $timezoneId,
        public ?string $timezoneName,
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
            name: $s->getName(),
            createdAt: $s->getCreatedAt(),
            instructorId: $s->getInstructor()?->getId(),
            instructorName: $s->getInstructor()?->getName(),
            isVirtualOnly: (bool) $s->isVirtualOnly(),
            venueId: $s->getVenue()?->getId(),
            venueName: $s->getVenue()?->getName(),
            timezoneId: $s->getTimezone()?->getId(),
            timezoneName: $s->getTimezone()?->getName(),
        );
    }
}

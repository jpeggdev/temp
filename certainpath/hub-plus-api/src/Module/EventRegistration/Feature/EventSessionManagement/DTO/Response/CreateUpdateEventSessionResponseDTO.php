<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Response;

use App\Entity\EventSession;

class CreateUpdateEventSessionResponseDTO
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
        public ?\DateTimeImmutable $updatedAt,
        public bool $isVirtualOnly,
        public ?int $venueId,
        public ?string $venueName,
        public ?int $timezoneId,
        public ?string $timezoneName,
    ) {
    }

    public static function fromEntity(EventSession $session): self
    {
        return new self(
            id: $session->getId(),
            uuid: $session->getUuid(),
            eventId: $session->getEvent()?->getId() ?? 0,
            startDate: $session->getStartDate(),
            endDate: $session->getEndDate(),
            maxEnrollments: $session->getMaxEnrollments(),
            virtualLink: $session->getVirtualLink(),
            notes: $session->getNotes(),
            isPublished: (bool) $session->getIsPublished(),
            createdAt: $session->getCreatedAt(),
            updatedAt: $session->getUpdatedAt(),
            isVirtualOnly: (bool) $session->isVirtualOnly(),
            venueId: $session->getVenue()?->getId(),
            venueName: $session->getVenue()?->getName(),
            timezoneId: $session->getTimezone()?->getId(),
            timezoneName: $session->getTimezone()?->getName(),
        );
    }
}

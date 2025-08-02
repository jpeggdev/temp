<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

use App\Entity\EventEnrollmentWaitlist;

final class MoveEnrollmentToWaitlistResponseDTO
{
    public function __construct(
        public int $waitlistId,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?\DateTimeImmutable $waitlistedAt,
    ) {
    }

    public static function fromEntity(EventEnrollmentWaitlist $waitlistItem): self
    {
        return new self(
            waitlistId: $waitlistItem->getId(),
            firstName: $waitlistItem->getFirstName(),
            lastName: $waitlistItem->getLastName(),
            email: $waitlistItem->getEmail(),
            waitlistedAt: $waitlistItem->getWaitlistedAt()
        );
    }
}

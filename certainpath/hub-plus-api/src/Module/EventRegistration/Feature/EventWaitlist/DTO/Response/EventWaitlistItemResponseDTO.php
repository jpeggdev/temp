<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

use App\Entity\EventEnrollmentWaitlist;

final class EventWaitlistItemResponseDTO
{
    public function __construct(
        public int $id,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?\DateTimeImmutable $waitlistedAt,
        public ?string $companyName,
        public ?int $waitlistPosition,
    ) {
    }

    public static function fromEntity(EventEnrollmentWaitlist $w): self
    {
        $company = $w->getOriginalCheckout()?->getCompany();
        $companyName = $company?->getCompanyName();

        return new self(
            id: $w->getId(),
            firstName: $w->getFirstName(),
            lastName: $w->getLastName(),
            email: $w->getEmail(),
            waitlistedAt: $w->getWaitlistedAt(),
            companyName: $companyName,
            waitlistPosition: $w->getWaitlistPosition(),
        );
    }
}

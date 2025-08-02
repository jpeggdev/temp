<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

use App\Entity\EventEnrollment;

final class EventEnrollmentItemResponseDTO
{
    public function __construct(
        public int $id,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $companyName,
        public ?int $companyId,
        public ?\DateTimeImmutable $enrolledAt,
        public array $replacements = [],
    ) {
    }

    public static function fromEntityWithReplacements(
        EventEnrollment $enrollment,
        array $replacements,
    ): self {
        $company = $enrollment->getEventCheckout()?->getCompany();

        return new self(
            id: $enrollment->getId(),
            firstName: $enrollment->getFirstName(),
            lastName: $enrollment->getLastName(),
            email: $enrollment->getEmail(),
            companyName: $company?->getCompanyName(),
            companyId: $company?->getId(),
            enrolledAt: $enrollment->getEnrolledAt(),
            replacements: $replacements
        );
    }
}

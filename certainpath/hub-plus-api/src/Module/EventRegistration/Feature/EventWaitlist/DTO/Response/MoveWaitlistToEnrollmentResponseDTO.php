<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

use App\Entity\EventEnrollment;

final class MoveWaitlistToEnrollmentResponseDTO
{
    public function __construct(
        public int $enrollmentId,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $companyName,
        public ?\DateTimeImmutable $enrolledAt,
    ) {
    }

    public static function fromEntity(EventEnrollment $enrollment): self
    {
        $company = $enrollment->getEventCheckout()?->getCompany();
        $companyName = $company?->getCompanyName();

        return new self(
            enrollmentId: $enrollment->getId(),
            firstName: $enrollment->getFirstName(),
            lastName: $enrollment->getLastName(),
            email: $enrollment->getEmail(),
            companyName: $companyName,
            enrolledAt: $enrollment->getEnrolledAt()
        );
    }
}

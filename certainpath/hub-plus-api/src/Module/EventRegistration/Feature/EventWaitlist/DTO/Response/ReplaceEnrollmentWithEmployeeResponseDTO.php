<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

use App\Entity\EventEnrollment;

final class ReplaceEnrollmentWithEmployeeResponseDTO
{
    public function __construct(
        public int $enrollmentId,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?int $employeeId,
    ) {
    }

    public static function fromEntity(EventEnrollment $enrollment): self
    {
        return new self(
            enrollmentId: $enrollment->getId(),
            firstName: $enrollment->getFirstName(),
            lastName: $enrollment->getLastName(),
            email: $enrollment->getEmail(),
            employeeId: $enrollment->getEmployee()?->getId()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ReplaceEnrollmentWithEmployeeRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event Enrollment ID is required.')]
        #[Assert\Type('numeric')]
        public int $eventEnrollmentId,
        #[Assert\NotBlank(message: 'Employee ID is required.')]
        #[Assert\Type('numeric')]
        public int $employeeId,
    ) {
    }
}

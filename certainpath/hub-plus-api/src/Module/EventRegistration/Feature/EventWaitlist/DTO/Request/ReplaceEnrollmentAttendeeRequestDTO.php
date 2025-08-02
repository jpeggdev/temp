<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class ReplaceEnrollmentAttendeeRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event Enrollment ID is required.')]
        #[Assert\Type('numeric')]
        public int $eventEnrollmentId,
        #[Assert\NotBlank(message: 'New first name is required.')]
        public string $newFirstName,
        #[Assert\NotBlank(message: 'New last name is required.')]
        public string $newLastName,
        #[Assert\NotBlank(message: 'New email is required.')]
        #[Assert\Email(message: 'Please enter a valid email address.')]
        public string $newEmail,
    ) {
    }
}

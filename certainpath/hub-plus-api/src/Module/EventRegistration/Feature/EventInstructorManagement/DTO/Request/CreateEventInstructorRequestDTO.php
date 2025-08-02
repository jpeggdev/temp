<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventInstructorManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateEventInstructorRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Instructor name should not be blank.')]
        public string $name,
        #[Assert\NotBlank(message: 'Instructor email should not be blank.')]
        #[Assert\Email(message: 'Instructor email must be a valid email address.')]
        public string $email,
        public ?string $phone = null,
    ) {
    }
}

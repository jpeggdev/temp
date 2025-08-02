<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class AttendeeDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The firstName field cannot be empty')]
        public string $firstName,
        #[Assert\NotBlank(message: 'The lastName field cannot be empty')]
        public string $lastName,
        #[Assert\Email(message: 'The email provided is invalid')]
        public ?string $email = null,
        public bool $isSelected = true,
        public ?string $specialRequests = null,
        public ?int $id = null,
    ) {
    }
}

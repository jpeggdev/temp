<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateEventCheckoutSessionRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event Session UUID should not be blank.')]
        #[Assert\Uuid(message: 'Event Session UUID must be a valid UUID.')]
        public string $eventSessionUuid,
    ) {
    }
}

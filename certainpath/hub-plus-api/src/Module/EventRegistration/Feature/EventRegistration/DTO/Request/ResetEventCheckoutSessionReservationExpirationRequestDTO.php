<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ResetEventCheckoutSessionReservationExpirationRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event Checkout Session UUID should not be blank.')]
        #[Assert\Uuid(message: 'Event Checkout Session UUID must be a valid UUID.')]
        public string $eventCheckoutSessionUuid,
    ) {
    }
}

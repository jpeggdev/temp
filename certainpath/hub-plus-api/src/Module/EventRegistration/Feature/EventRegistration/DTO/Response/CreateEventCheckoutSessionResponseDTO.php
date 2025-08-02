<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class CreateEventCheckoutSessionResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $reservationExpiresAt,
    ) {
    }
}

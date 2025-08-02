<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class AttendeeResponseDTO
{
    public function __construct(
        public ?int $id,
        public string $firstName,
        public string $lastName,
        public ?string $email,
        public ?string $specialRequests,
    ) {
    }
}

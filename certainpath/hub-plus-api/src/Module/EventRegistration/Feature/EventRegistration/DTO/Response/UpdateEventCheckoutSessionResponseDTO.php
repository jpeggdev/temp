<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class UpdateEventCheckoutSessionResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $contactName,
        public string $contactEmail,
        public ?string $contactPhone,
        public ?string $groupNotes,
        /**
         * @param array<AttendeeResponseDTO> $attendees
         */
        public array $attendees = [],
    ) {
    }
}

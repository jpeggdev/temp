<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Response;

final class UpdateEventCheckoutAttendeeWaitlistResponseDTO
{
    public function __construct(
        /**
         * An array of attendees with updated waitlist status
         * [
         *   'id' => int,
         *   'email' => string,
         *   'firstName' => string,
         *   'lastName' => string,
         *   'specialRequests' => string|null,
         *   'isSelected' => bool,
         *   'isWaitlist' => bool,
         * ].
         */
        public array $attendees,
        public bool $success,
        public ?string $message = null,
    ) {
    }
}

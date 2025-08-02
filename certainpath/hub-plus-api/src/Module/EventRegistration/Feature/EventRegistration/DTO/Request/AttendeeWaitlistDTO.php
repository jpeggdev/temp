<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class AttendeeWaitlistDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'The attendeeId field cannot be null')]
        #[Assert\Type('integer', message: 'The attendeeId must be an integer')]
        #[Assert\GreaterThan(value: 0, message: 'The attendeeId must be greater than 0')]
        public int $attendeeId,
        #[Assert\NotNull(message: 'The isWaitlist field cannot be null')]
        #[Assert\Type('boolean', message: 'The isWaitlist field must be a boolean')]
        public bool $isWaitlist,
    ) {
    }
}

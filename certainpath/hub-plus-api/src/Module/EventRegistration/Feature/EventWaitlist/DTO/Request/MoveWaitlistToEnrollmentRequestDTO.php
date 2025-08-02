<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class MoveWaitlistToEnrollmentRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Waitlist ID is required.')]
        #[Assert\Type('numeric')]
        public int $eventWaitlistId,
    ) {
    }
}

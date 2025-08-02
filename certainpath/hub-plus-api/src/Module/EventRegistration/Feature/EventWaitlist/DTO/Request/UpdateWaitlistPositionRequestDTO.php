<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateWaitlistPositionRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Event Waitlist ID is required.')]
        #[Assert\Type('numeric')]
        public int $eventWaitlistId,
        #[Assert\NotBlank(message: 'New position must not be blank.')]
        #[Assert\GreaterThanOrEqual(1)]
        public int $newPosition,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class RemoveWaitlistItemRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'EventWaitlist ID is required.')]
        #[Assert\Type('numeric')]
        public int $eventWaitlistId,
    ) {
    }
}

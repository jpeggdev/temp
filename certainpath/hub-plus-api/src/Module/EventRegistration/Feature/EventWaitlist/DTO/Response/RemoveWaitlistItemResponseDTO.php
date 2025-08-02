<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

final class RemoveWaitlistItemResponseDTO
{
    public function __construct(
        public string $message,
    ) {
    }

    public static function success(int $waitlistItemId): self
    {
        return new self(
            message: sprintf('Waitlist item %d removed successfully.', $waitlistItemId)
        );
    }
}

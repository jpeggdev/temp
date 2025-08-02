<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response;

class GetWaitlistDetailsResponseDTO
{
    public function __construct(
        public int $id,
        public string $uuid,
        public ?string $name,
        public ?\DateTimeImmutable $startDate,
        public ?\DateTimeImmutable $endDate,
        public ?string $timezoneShortName,
        public ?string $timezoneIdentifier,
        public int $waitlistCount,
        public int $enrolledCount,
        public int $checkoutReservedCount,
        public int $availableSeatCount,
        public int $maxEnrollments,
    ) {
    }
}

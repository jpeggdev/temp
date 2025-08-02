<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventSessionManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUpdateEventSessionRequestDTO
{
    public function __construct(
        #[Assert\NotNull(message: 'eventUuid is required')]
        public ?string $eventUuid = null,
        public ?\DateTimeImmutable $startDate = null,
        public ?\DateTimeImmutable $endDate = null,
        #[Assert\NotNull(message: 'maxEnrollments is required')]
        #[Assert\GreaterThanOrEqual(0, message: 'maxEnrollments must be >= 0')]
        public ?int $maxEnrollments = 0,
        public ?string $virtualLink = null,
        public ?string $notes = null,
        public ?bool $isPublished = false,
        #[Assert\NotBlank(message: 'name is required')]
        public ?string $name = null,
        public ?int $instructorId = null,
        public ?bool $isVirtualOnly = false,
        public ?int $venueId = null,
        #[Assert\NotNull(message: 'timezone is required')]
        public ?int $timezoneId = null,
    ) {
    }
}

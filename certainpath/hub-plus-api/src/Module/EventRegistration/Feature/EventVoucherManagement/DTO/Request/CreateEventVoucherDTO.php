<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventVoucherManagement\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateEventVoucherDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        #[Assert\Type(type: 'string', message: 'The name field must be a string.')]
        public string $name,
        #[Assert\NotBlank]
        #[Assert\Type(type: 'string', message: 'The companyIdentifier field must be a string.')]
        public string $companyIdentifier,
        #[Assert\Type(type: 'string', message: 'The description field must be a string.')]
        public ?string $description = null,
        #[Assert\NotNull(message: 'The totalSeats field is required.')]
        #[Assert\GreaterThanOrEqual(0, message: 'The totalSeats field must be >= 0.')]
        public ?int $totalSeats = 0,
        #[Assert\Type(type: 'boolean', message: 'The isActive field must be a boolean.')]
        public bool $isActive = true,
        public ?\DateTimeImmutable $startDate = null,
        public ?\DateTimeImmutable $endDate = null,
    ) {
    }
}

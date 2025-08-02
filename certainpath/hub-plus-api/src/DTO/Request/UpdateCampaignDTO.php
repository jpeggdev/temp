<?php

declare(strict_types=1);

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCampaignDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The name field cannot be empty')]
        public ?string $name = null,
        public ?string $description = null,
        #[Assert\Regex(pattern: '/^\d{3}-\d{3}-\d{4}$/', message: 'Invalid phone number format')]
        public ?string $phoneNumber = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $mailingFrequencyWeeks = null,
        #[Assert\NotBlank(message: 'The status field cannot be empty')]
        public ?int $status = null,
    ) {
    }
}

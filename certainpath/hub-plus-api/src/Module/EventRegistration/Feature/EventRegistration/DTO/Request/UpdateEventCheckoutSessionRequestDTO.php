<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateEventCheckoutSessionRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The contactName field cannot be empty')]
        public ?string $contactName = null,
        #[Assert\NotBlank(message: 'The contactEmail field cannot be empty')]
        #[Assert\Email(message: 'The contactEmail provided is invalid')]
        public ?string $contactEmail = null,
        public ?string $contactPhone = null,
        public ?string $groupNotes = null,
        /**
         * @var AttendeeDTO[]
         */
        #[Assert\Type('array', message: 'attendees must be an array')]
        #[Assert\Valid]
        public array $attendees = [],
    ) {
    }
}

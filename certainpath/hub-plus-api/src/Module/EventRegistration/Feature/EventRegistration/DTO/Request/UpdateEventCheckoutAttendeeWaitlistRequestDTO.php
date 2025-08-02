<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class UpdateEventCheckoutAttendeeWaitlistRequestDTO
{
    /**
     * @var AttendeeWaitlistDTO[]
     */
    #[Assert\NotNull(message: 'The attendees field cannot be null')]
    #[Assert\Type('array', message: 'attendees must be an array')]
    #[Assert\Count(min: 1, minMessage: 'At least one attendee must be provided')]
    #[Assert\Valid]
    public array $attendees = [];

    public function __construct(array $attendees = [])
    {
        $this->attendees = $attendees;
    }
}

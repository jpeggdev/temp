<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\GetWaitlistDetailsResponseDTO;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventEnrollmentRepository;

readonly class GetWaitlistDetailsService
{
    public function __construct(
        private EventCheckoutAttendeeRepository $eventCheckoutAttendeeRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
    ) {
    }

    public function getWaitlistDetails(EventSession $eventSession): GetWaitlistDetailsResponseDTO
    {
        $maxEnrollments = $eventSession->getMaxEnrollments();

        $checkoutReservedCount = $this->eventCheckoutAttendeeRepository
            ->countActiveAttendeesForSession($eventSession);

        $enrolledCount = $this->eventEnrollmentRepository
            ->countEnrollmentsForSession($eventSession);

        $occupiedSeats = $checkoutReservedCount + $enrolledCount;
        $availableSeatCount = max(0, $maxEnrollments - $occupiedSeats);

        $name = $eventSession->getName();

        $timezoneShortName = $eventSession->getTimezone()?->getShortName();
        $timezoneIdentifier = $eventSession->getTimezone()?->getIdentifier();

        $waitlists = $eventSession->getEventEnrollmentWaitlists();
        $waitlistCount = count($waitlists);

        return new GetWaitlistDetailsResponseDTO(
            id: $eventSession->getId(),
            uuid: $eventSession->getUuid(),
            name: $name,
            startDate: $eventSession->getStartDate(),
            endDate: $eventSession->getEndDate(),
            timezoneShortName: $timezoneShortName,
            timezoneIdentifier: $timezoneIdentifier,
            waitlistCount: $waitlistCount,
            enrolledCount: $enrolledCount,
            checkoutReservedCount: $checkoutReservedCount,
            availableSeatCount: $availableSeatCount,
            maxEnrollments: $maxEnrollments,
        );
    }
}

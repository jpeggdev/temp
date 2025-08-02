<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventSession;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventEnrollmentRepository;

/**
 * A dedicated service to calculate seat usage and availability for an EventSession,
 * including how many seats the current user has occupied.
 */
final readonly class EventSessionSeatCalculatorService
{
    public function __construct(
        private EventCheckoutAttendeeRepository $eventCheckoutAttendeeRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
    ) {
    }

    /**
     * Calculate seat usage details for the given session, including:
     *  - occupiedAttendeeSeats (in-progress, not expired, isSelected)
     *  - occupiedEnrolledSeats (fully enrolled seats)
     *  - occupiedAttendeeSeatsByCurrentUser
     *  - occupiedSeats (sum of in-progress + enrolled)
     *  - maxEnrollments
     *  - availableSeats (maxEnrollments - occupiedSeats)
     *
     *  If the session is null or has no maxEnrollments data, you can decide to return 0 or handle differently.
     */
    public function calculate(
        EventSession $eventSession,
        Company $company,
        Employee $employee,
    ): array {
        $maxEnrollments = $eventSession->getMaxEnrollments();

        // 1) Find how many seats are actively occupied by ANY user
        $occupiedAttendeeSeats = $this->eventCheckoutAttendeeRepository
            ->countActiveAttendeesForSession($eventSession);

        // 2) Fully enrolled seats
        $occupiedEnrolledSeats = $this->eventEnrollmentRepository
            ->countEnrollmentsForSession($eventSession);

        // 3) Seats specifically held by this user (in-progress, not expired)
        $occupiedAttendeeSeatsByCurrentUser = $this->eventCheckoutAttendeeRepository
            ->countActiveAttendeesForSessionByEmployee(
                $eventSession,
                $employee,
                $company
            );

        // 4) Combine to get total seats
        $occupiedSeats = $occupiedAttendeeSeats + $occupiedEnrolledSeats;
        $availableSeats = max(0, $maxEnrollments - $occupiedSeats);

        return [
            'occupiedAttendeeSeats' => $occupiedAttendeeSeats,
            'occupiedEnrolledSeats' => $occupiedEnrolledSeats,
            'occupiedAttendeeSeatsByCurrentUser' => $occupiedAttendeeSeatsByCurrentUser,
            'occupiedSeats' => $occupiedSeats,
            'maxEnrollments' => $maxEnrollments,
            'availableSeats' => $availableSeats,
        ];
    }
}

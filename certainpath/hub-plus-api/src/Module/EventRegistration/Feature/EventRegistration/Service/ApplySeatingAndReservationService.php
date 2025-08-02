<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\EventCheckout;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventEnrollmentRepository;

final readonly class ApplySeatingAndReservationService
{
    public function __construct(
        private EventCheckoutAttendeeRepository $eventCheckoutAttendeeRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
    ) {
    }

    public function apply(EventCheckout $eventCheckout): void
    {
        $eventSession = $eventCheckout->getEventSession();
        if (!$eventSession) {
            return;
        }

        if (0 === $eventCheckout->getEventCheckoutAttendees()->count()) {
            $eventCheckout->setReservationExpiresAt(null);

            return;
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $otherInProgressCount = $this->eventCheckoutAttendeeRepository->countInProgressAttendees(
            sessionId: $eventSession->getId(),
            excludeCheckoutId: $eventCheckout->getId(),
            now: $now
        );
        $alreadyEnrolledCount = $this->eventEnrollmentRepository->countEnrollmentsForSession($eventSession);
        $maxEnrollments = $eventSession->getMaxEnrollments();

        $usedSeats = $otherInProgressCount + $alreadyEnrolledCount;
        $remainingCapacity = max(0, $maxEnrollments - $usedSeats);

        $i = 0;
        foreach ($eventCheckout->getEventCheckoutAttendees() as $attendee) {
            if ($i < $remainingCapacity) {
                $attendee->setIsWaitlist(false);
                ++$i;
            } else {
                $attendee->setIsWaitlist(true);
            }
        }

        $anySeated = false;
        foreach ($eventCheckout->getEventCheckoutAttendees() as $a) {
            if (!$a->isWaitlist()) {
                $anySeated = true;
                break;
            }
        }

        if ($anySeated) {
            $existingExpires = $eventCheckout->getReservationExpiresAt();
            if (!$existingExpires || $existingExpires <= $now) {
                $eventCheckout->setReservationExpiresAt($now->modify('+30 minutes'));
            }
        } else {
            $eventCheckout->setReservationExpiresAt(null);
        }
    }
}

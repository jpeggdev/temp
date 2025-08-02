<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EventCheckoutSessionNotFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NotEnoughSeatsAvailableException;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventEnrollmentRepository;

final readonly class SeatsAvailabilityValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private EventEnrollmentRepository $eventEnrollmentRepository,
        private EventCheckoutAttendeeRepository $eventCheckoutAttendeeRepository,
    ) {
    }

    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        $eventSession = $eventCheckout->getEventSession();
        if (!$eventSession) {
            throw new EventCheckoutSessionNotFoundException();
        }

        $maxEnrollments = $eventSession->getMaxEnrollments();
        $alreadyEnrolledCount = $this->eventEnrollmentRepository->countEnrollmentsForSession($eventSession);

        $now = new \DateTimeImmutable();
        $inProgressCount = $this->eventCheckoutAttendeeRepository->countInProgressAttendees(
            $eventSession->getId(),
            $eventCheckout->getId(),
            $now
        );

        $seatsInUse = $alreadyEnrolledCount + $inProgressCount;
        $seatsAvailable = $maxEnrollments - $seatsInUse;

        $allAttendees = $eventCheckout->getEventCheckoutAttendees()->toArray();
        $nonWaitlistedSelected = array_filter($allAttendees, function ($attendee) {
            return $attendee->isSelected() && !$attendee->isWaitlist();
        });
        $needed = count($nonWaitlistedSelected);

        if ($needed > $seatsAvailable) {
            throw new NotEnoughSeatsAvailableException($seatsAvailable);
        }
    }
}

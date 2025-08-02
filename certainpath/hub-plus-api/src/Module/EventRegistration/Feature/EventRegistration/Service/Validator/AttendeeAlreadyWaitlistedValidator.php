<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\AttendeeAlreadyWaitlistedException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventSessionFoundException;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentWaitlistRepository;

final readonly class AttendeeAlreadyWaitlistedValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private EventEnrollmentWaitlistRepository $eventEnrollmentWaitlistRepository,
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
            throw new NoEventSessionFoundException();
        }

        foreach ($eventCheckout->getEventCheckoutAttendees() as $attendee) {
            $email = $attendee->getEmail();
            if (!$email) {
                continue;
            }

            $employeeMatch = $this->employeeRepository
                ->findOneMatchingEmailAndCompany($email, $eventCheckout->getCompany());

            if ($employeeMatch) {
                $existingWaitlisted = $this->eventEnrollmentWaitlistRepository
                    ->findOneByEventSessionAndEmployee($eventSession->getId(), $employeeMatch->getId());

                if ($existingWaitlisted) {
                    throw new AttendeeAlreadyWaitlistedException(sprintf('Employee with email %s is already waitlisted for this session.', $email));
                }
            } else {
                $existingByEmail = $this->eventEnrollmentWaitlistRepository
                    ->findOneByEventSessionAndEmail($eventSession->getId(), $email);

                if ($existingByEmail) {
                    throw new AttendeeAlreadyWaitlistedException(sprintf('Attendee with email %s is already waitlisted for this session.', $email));
                }
            }
        }
    }
}

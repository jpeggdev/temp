<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\AttendeeAlreadyEnrolledException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EmployeeAlreadyEnrolledException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventSessionFoundException;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentRepository;

final readonly class AttendeeAlreadyEnrolledValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private EventEnrollmentRepository $eventEnrollmentRepository,
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
                $existingEnrollment = $this->eventEnrollmentRepository
                    ->findOneByEventSessionAndEmployee($eventSession->getId(), $employeeMatch->getId());

                if ($existingEnrollment) {
                    throw new EmployeeAlreadyEnrolledException($email);
                }
            } else {
                $existingByEmail = $this->eventEnrollmentRepository
                    ->findOneByEventSessionAndEmail($eventSession->getId(), $email);

                if ($existingByEmail) {
                    throw new AttendeeAlreadyEnrolledException($email);
                }
            }
        }
    }
}

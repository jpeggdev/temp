<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventEnrollment;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Repository\EmployeeRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Creates enrollments for each attendee who is NOT waitlisted.
 */
final readonly class EnrollmentPostProcessor implements EventCheckoutPostProcessorInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function postProcess(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): void {
        $eventSession = $eventCheckout->getEventSession();
        if (!$eventSession) {
            return;
        }

        $attendees = $eventCheckout->getEventCheckoutAttendees();
        foreach ($attendees as $attendee) {
            if ($attendee->isWaitlist()) {
                continue;
            }

            $email = $attendee->getEmail();
            $employeeMatch = null;

            if ($email) {
                $employeeMatch = $this->employeeRepository
                    ->findOneMatchingEmailAndCompany($email, $eventCheckout->getCompany());
            }

            $enrollment = new EventEnrollment();
            $enrollment->setEventCheckout($eventCheckout);
            $enrollment->setEventSession($eventSession);
            $enrollment->setEnrolledAt(new \DateTimeImmutable());
            $enrollment->setEmployee($employeeMatch);
            $enrollment->setFirstName($attendee->getFirstName());
            $enrollment->setLastName($attendee->getLastName());
            $enrollment->setEmail($attendee->getEmail());
            $enrollment->setSpecialRequests($attendee->getSpecialRequests());

            $this->entityManager->persist($enrollment);
        }
    }
}

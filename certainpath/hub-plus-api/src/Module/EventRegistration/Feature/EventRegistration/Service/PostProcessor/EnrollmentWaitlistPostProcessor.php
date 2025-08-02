<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventEnrollmentWaitlist;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Creates waitlist entries for each attendee who IS waitlisted.
 */
final readonly class EnrollmentWaitlistPostProcessor implements EventCheckoutPostProcessorInterface
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private EventEnrollmentWaitlistRepository $eventEnrollmentWaitlistRepository,
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

        $maxPosition = $this->eventEnrollmentWaitlistRepository->getMaxWaitlistPosition($eventSession);
        if (null === $maxPosition) {
            $maxPosition = 0;
        }

        $attendees = $eventCheckout->getEventCheckoutAttendees();
        foreach ($attendees as $attendee) {
            if (!$attendee->isWaitlist()) {
                continue;
            }

            $email = $attendee->getEmail();
            $employeeMatch = null;
            if ($email) {
                $employeeMatch = $this->employeeRepository->findOneMatchingEmailAndCompany(
                    $email,
                    $eventCheckout->getCompany()
                );
            }

            ++$maxPosition;

            $waitlistEntry = new EventEnrollmentWaitlist();
            $waitlistEntry->setEventSession($eventSession);
            $waitlistEntry->setWaitlistedAt(new \DateTimeImmutable());
            $waitlistEntry->setEmployee($employeeMatch);
            $waitlistEntry->setFirstName($attendee->getFirstName());
            $waitlistEntry->setLastName($attendee->getLastName());
            $waitlistEntry->setEmail($attendee->getEmail());
            $waitlistEntry->setSpecialRequests($attendee->getSpecialRequests());
            $waitlistEntry->setOriginalCheckout($eventCheckout);
            $waitlistEntry->setWaitlistPosition($maxPosition);

            $waitlistEntry->setSeatPrice((string) $eventSession->getEvent()->getEventPrice());

            $this->entityManager->persist($waitlistEntry);
        }
    }
}

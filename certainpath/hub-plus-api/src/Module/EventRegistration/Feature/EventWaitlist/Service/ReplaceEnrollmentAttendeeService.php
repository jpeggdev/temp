<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\EventEnrollment;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\ReplaceEnrollmentAttendeeRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\ReplaceEnrollmentAttendeeResponseDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\EventEnrollmentNotFoundException;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ReplaceEnrollmentAttendeeService
{
    public function __construct(
        private EventEnrollmentRepository $enrollmentRepository,
        private EmployeeRepository $employeeRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function replaceAttendee(
        EventSession $session,
        ReplaceEnrollmentAttendeeRequestDTO $dto,
    ): ReplaceEnrollmentAttendeeResponseDTO {
        $enrollment = $this->enrollmentRepository->findOneByIdAndSession(
            $dto->eventEnrollmentId,
            $session->getId()
        );

        if (!$enrollment instanceof EventEnrollment) {
            throw EventEnrollmentNotFoundException::forEnrollmentAndSession($dto->eventEnrollmentId, $session->getId());
        }

        $company = $enrollment->getEventCheckout()?->getCompany();
        $employeeMatch = null;
        if ($company && $dto->newEmail) {
            $employeeMatch = $this->employeeRepository->findOneMatchingEmailAndCompany(
                $dto->newEmail,
                $company
            );
        }

        $enrollment->setFirstName($dto->newFirstName);
        $enrollment->setLastName($dto->newLastName);
        $enrollment->setEmail($dto->newEmail);
        $enrollment->setEmployee($employeeMatch);

        $this->em->flush();

        return ReplaceEnrollmentAttendeeResponseDTO::fromEntity($enrollment);
    }
}

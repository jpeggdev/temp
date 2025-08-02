<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Service;

use App\Entity\Employee;
use App\Entity\EventEnrollment;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Request\ReplaceEnrollmentWithEmployeeRequestDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\DTO\Response\ReplaceEnrollmentWithEmployeeResponseDTO;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\EmployeeNotFoundException;
use App\Module\EventRegistration\Feature\EventWaitlist\Exception\EventEnrollmentNotFoundException;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ReplaceEnrollmentWithEmployeeService
{
    public function __construct(
        private EventEnrollmentRepository $enrollmentRepository,
        private EmployeeRepository $employeeRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function replaceAttendeeWithEmployee(
        EventSession $session,
        ReplaceEnrollmentWithEmployeeRequestDTO $dto,
    ): ReplaceEnrollmentWithEmployeeResponseDTO {
        $enrollment = $this->enrollmentRepository->findOneByIdAndSession(
            $dto->eventEnrollmentId,
            $session->getId()
        );
        if (!$enrollment instanceof EventEnrollment) {
            throw EventEnrollmentNotFoundException::forEnrollmentAndSession($dto->eventEnrollmentId, $session->getId());
        }

        $employee = $this->employeeRepository->find($dto->employeeId);
        if (!$employee instanceof Employee) {
            throw EmployeeNotFoundException::forEmployeeId($dto->employeeId);
        }

        $enrollment->setEmployee($employee);
        $enrollment->setFirstName($employee->getFirstName());
        $enrollment->setLastName($employee->getLastName());
        $enrollment->setEmail($employee->getUser()->getEmail() ?? '');

        $this->em->flush();

        return ReplaceEnrollmentWithEmployeeResponseDTO::fromEntity($enrollment);
    }
}

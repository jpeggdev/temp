<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\LoggedInUserDTO;
use App\DTO\Request\UpdateMyUserProfileRequestDTO;
use App\Entity\Employee;
use App\Exception\DuplicateEmployeeWorkEmailException;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateMyUserProfileService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateMyUserProfile(
        LoggedInUserDTO $loggedInUserDTO,
        UpdateMyUserProfileRequestDTO $updateRequest,
    ): void {
        $employee = $loggedInUserDTO->getActiveEmployee();

        $existingEmployee = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(Employee::class, 'e')
            ->where('e.workEmail = :workEmail')
            ->andWhere('e.id != :employeeId')
            ->setParameter('workEmail', $updateRequest->workEmail)
            ->setParameter('employeeId', $employee->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (null !== $existingEmployee) {
            throw new DuplicateEmployeeWorkEmailException();
        }

        $employee->setFirstName($updateRequest->firstName);
        $employee->setLastName($updateRequest->lastName);
        $employee->setWorkEmail($updateRequest->workEmail);

        $this->entityManager->persist($employee);
        $this->entityManager->flush();
    }
}

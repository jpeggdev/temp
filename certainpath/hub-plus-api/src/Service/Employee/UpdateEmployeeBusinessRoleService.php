<?php

declare(strict_types=1);

namespace App\Service\Employee;

use App\DTO\Request\UpdateEmployeeBusinessRoleDTO;
use App\Entity\Employee;
use App\Exception\BusinessRoleNotFoundException;
use App\Repository\BusinessRoleRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEmployeeBusinessRoleService
{
    public function __construct(
        private BusinessRoleRepository $businessRoleRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateEmployeeBusinessRole(Employee $employee, UpdateEmployeeBusinessRoleDTO $dto): Employee
    {
        $businessRole = $this->businessRoleRepository->find($dto->businessRoleId);

        if (!$businessRole) {
            throw new BusinessRoleNotFoundException();
        }

        $employee->setRole($businessRole);

        $this->entityManager->flush();

        return $employee;
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Employee;

use App\DTO\Request\UpdateEmployeePermissionDTO;
use App\Entity\Employee;
use App\Entity\EmployeePermission;
use App\Exception\EmployeePermissionNotFoundException;
use App\Exception\PermissionNotFoundException;
use App\Repository\EmployeePermissionRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEmployeePermissionService
{
    public function __construct(
        private PermissionRepository $permissionRepository,
        private EmployeePermissionRepository $employeePermissionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function updateEmployeePermission(Employee $employee, UpdateEmployeePermissionDTO $dto): void
    {
        $permission = $this->permissionRepository->find($dto->permissionId);

        if (!$permission) {
            throw new PermissionNotFoundException();
        }

        if ($dto->active) {
            $existingPermission = $this->employeePermissionRepository->findOneBy([
                'employee' => $employee,
                'permission' => $permission,
            ]);

            if (!$existingPermission) {
                $employeePermission = new EmployeePermission();
                $employeePermission->setEmployee($employee);
                $employeePermission->setPermission($permission);

                $this->entityManager->persist($employeePermission);
                $this->entityManager->flush();
            }
        } else {
            $employeePermission = $this->employeePermissionRepository->findOneBy([
                'employee' => $employee,
                'permission' => $permission,
            ]);

            if (!$employeePermission) {
                throw new EmployeePermissionNotFoundException();
            }

            $this->entityManager->remove($employeePermission);
            $this->entityManager->flush();
        }
    }
}

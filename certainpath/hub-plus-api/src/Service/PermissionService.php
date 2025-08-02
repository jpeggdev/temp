<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Employee;
use App\Entity\EmployeePermission;
use App\Entity\Permission;
use App\Repository\BusinessRolePermissionRepository;
use App\Repository\EmployeePermissionRepository;

class PermissionService
{
    private EmployeePermissionRepository $employeePermissionRepository;
    private BusinessRolePermissionRepository $businessRolePermissionRepository;

    public function __construct(
        EmployeePermissionRepository $employeePermissionRepository,
        BusinessRolePermissionRepository $businessRolePermissionRepository,
    ) {
        $this->employeePermissionRepository = $employeePermissionRepository;
        $this->businessRolePermissionRepository = $businessRolePermissionRepository;
    }

    /**
     * Check if the given employee has a specific permission.
     */
    public function hasPermission(Employee $employee, string $permissionInternalName): bool
    {
        // Check Employee-specific (adhoc) permissions
        $adhocPermissions = $this->employeePermissionRepository->findByEmployeeAndPermissionInternalName(
            $employee,
            $permissionInternalName
        );

        if (!empty($adhocPermissions)) {
            return true;
        }

        // Check Role-based permissions
        $rolePermissions = $this->businessRolePermissionRepository->findByRoleAndPermissionInternalName(
            $employee->getRole(),
            $permissionInternalName
        );

        return !empty($rolePermissions);
    }

    /**
     * Check if the given employee has a certain role by its internal name.
     */
    public function hasRole(Employee $employee, string $roleInternalName): bool
    {
        $role = $employee->getRole();
        if (!$role) {
            return false;
        }

        return $role->getInternalName() === $roleInternalName;
    }

    /**
     * @return Permission[]
     */
    public function getEmployeeRolePermissions(Employee $employee): array
    {
        $role = $employee->getRole();

        if (!$role) {
            return [];
        }

        $rolePermissions = $role->getRolePermissions()->toArray();

        return array_map(function ($rolePermission) {
            return $rolePermission->getPermission();
        }, $rolePermissions);
    }

    /**
     * @return Permission[]
     */
    public function getEmployeeAdditionalPermissions(Employee $employee): array
    {
        $employeePermissions = $this->employeePermissionRepository->findBy(['employee' => $employee]);

        return array_map(function (EmployeePermission $employeePermission) {
            return $employeePermission->getPermission();
        }, $employeePermissions);
    }

    /**
     * @return string[]
     */
    public function getAllPermissionInternalNamesForEmployee(Employee $employee): array
    {
        $rolePermissions = $this->getEmployeeRolePermissions($employee);

        $additionalPermissions = $this->getEmployeeAdditionalPermissions($employee);

        $allPermissions = array_merge(
            array_map(fn ($permission) => $permission->getInternalName(), $rolePermissions),
            array_map(fn ($permission) => $permission->getInternalName(), $additionalPermissions)
        );

        return array_values(array_unique($allPermissions));
    }

    /**
     * @return array<Application>
     */
    public function getApplicationAccessForEmployee(Employee $employee): array
    {
        return array_map(function ($access) {
            return $access->getApplication();
        }, $employee->getApplicationAccesses()->toArray());
    }
}

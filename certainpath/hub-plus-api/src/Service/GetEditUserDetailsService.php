<?php

namespace App\Service;

use App\DTO\Response\GetEditUserDetailsDTO;
use App\Entity\Application;
use App\Entity\BusinessRole;
use App\Entity\Employee;
use App\Entity\PermissionGroup;
use Doctrine\ORM\EntityManagerInterface;

readonly class GetEditUserDetailsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PermissionService $permissionService,
    ) {
    }

    public function getEditUserDetails(Employee $employee): GetEditUserDetailsDTO
    {
        $user = $employee->getUser();
        $userDetails = new GetEditUserDetailsDTO(
            $employee->getFirstName(),
            $employee->getLastName(),
            $user->getEmail(),
            $employee->getUuid(),
            $employee->getRole()?->getId()
        );

        $userDetails->setAvailableApplications($this->getAvailableApplications());
        $userDetails->setAvailableRoles($this->getAvailableRoles($employee));
        $userDetails->setAvailablePermissions($this->getAvailablePermissions($employee));
        $userDetails->setEmployeeApplicationAccess($this->getEmployeeApplicationAccess($employee));
        $userDetails->setEmployeeRolePermissions($this->getEmployeeRolePermissions($employee));
        $userDetails->setEmployeeAdditionalPermissions($this->getEmployeeAdditionalPermissions($employee));

        return $userDetails;
    }

    private function getAvailableApplications(): array
    {
        $applications = $this->entityManager->getRepository(Application::class)
            ->findBy([], ['id' => 'ASC']);

        return array_map(static function (Application $application) {
            return [
                'id' => $application->getId(),
                'name' => $application->getName(),
            ];
        }, $applications);
    }

    private function getAvailableRoles(Employee $employee): array
    {
        $company = $employee->getCompany();
        $isCertainPathCompany = $company->isCertainPath();

        $roles = $this->entityManager
            ->getRepository(BusinessRole::class)
            ->findBy([], ['sortOrder' => 'ASC']);
        $filteredRoles = array_filter($roles, function (BusinessRole $role) use ($isCertainPathCompany) {
            if (!$isCertainPathCompany && $role->isCertainPath()) {
                return false;
            }

            return true;
        });

        $businessRoles = array_map(function (BusinessRole $role) {
            return [
                'id' => $role->getId(),
                'name' => $role->getInternalName(),
                'label' => $role->getLabel(),
                'description' => $role->getDescription(),
                'isCertainPathOnly' => $role->isCertainPath(),
            ];
        }, $filteredRoles);

        return array_values($businessRoles);
    }

    private function getAvailablePermissions(Employee $employee): array
    {
        $company = $employee->getCompany();
        $isCertainPathCompany = $company->isCertainPath();

        $permissionGroups = $this->entityManager->getRepository(PermissionGroup::class)->findAll();

        // Filter permission groups based on the company's Certain Path status
        $filteredGroups = array_filter(array_map(function (PermissionGroup $group) use ($isCertainPathCompany) {
            // Filter permissions inside the group
            $filteredPermissions = array_filter(
                $group->getPermissions()->toArray(),
                function ($permission) use ($isCertainPathCompany) {
                    // If the company is a Certain Path company, include all permissions
                    // If not, exclude permissions that are Certain Path only
                    return $isCertainPathCompany || !$permission->isCertainPath();
                }
            );

            if (empty($filteredPermissions)) {
                return null;
            }

            if (!$isCertainPathCompany && $group->isCertainPath()) {
                return null;
            }

            return [
                'groupName' => $group->getName(),
                'description' => $group->getDescription(),
                'permissions' => array_map(static function ($permission) {
                    return [
                        'permissionId' => $permission->getId(),
                        'name' => $permission->getInternalName(),
                        'label' => $permission->getLabel(),
                        'description' => $permission->getDescription(),
                        'isCertainPathOnly' => $permission->isCertainPath(),
                    ];
                }, array_values($filteredPermissions)), // Reindex permissions
            ];
        }, $permissionGroups));

        return array_values($filteredGroups);
    }

    private function getEmployeeApplicationAccess(Employee $employee): array
    {
        $applications = $this->permissionService->getApplicationAccessForEmployee($employee);

        return array_map(static function (Application $application) {
            return [
                'applicationId' => $application->getId(),
                'applicationName' => $application->getName(),
            ];
        }, $applications);
    }

    private function getEmployeeRolePermissions(Employee $employee): array
    {
        $permissions = $this->permissionService->getEmployeeRolePermissions($employee);

        return array_map(static function ($permission) {
            return $permission->getId();
        }, $permissions);
    }

    private function getEmployeeAdditionalPermissions(Employee $employee): array
    {
        $permissions = $this->permissionService->getEmployeeAdditionalPermissions($employee);

        return array_map(static function ($permission) {
            return $permission->getId();
        }, $permissions);
    }
}

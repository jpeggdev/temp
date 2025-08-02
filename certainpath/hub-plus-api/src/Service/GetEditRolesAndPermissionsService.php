<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Response\GetEditRolesAndPermissionsResponseDTO;
use App\Entity\BusinessRole;
use App\Entity\Permission;
use Doctrine\ORM\EntityManagerInterface;

readonly class GetEditRolesAndPermissionsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getEditRolesAndPermissions(): GetEditRolesAndPermissionsResponseDTO
    {
        $roles = $this->entityManager
            ->getRepository(BusinessRole::class)
            ->findBy([], ['sortOrder' => 'ASC']);
        $permissions = $this->entityManager->getRepository(Permission::class)->findAll();

        $rolesData = array_map(function (BusinessRole $role) {
            $permissions = $role->getRolePermissions()->map(function ($rolePermission) {
                $permission = $rolePermission->getPermission();

                return [
                    'id' => $permission->getId(),
                    'name' => $permission->getInternalName(),
                    'label' => $permission->getLabel(),
                    'description' => $permission->getDescription(),
                    'isCertainPathOnly' => $permission->isCertainPath(),
                ];
            })->toArray();

            return [
                'id' => $role->getId(),
                'name' => $role->getInternalName(),
                'label' => $role->getLabel(),
                'description' => $role->getDescription(),
                'isCertainPathOnly' => $role->isCertainPath(),
                'permissions' => $permissions,
            ];
        }, $roles);

        $rolesData = array_values($rolesData);

        $permissionsData = array_map(function (Permission $permission) {
            return [
                'id' => $permission->getId(),
                'name' => $permission->getInternalName(),
                'label' => $permission->getLabel(),
                'description' => $permission->getDescription(),
                'isCertainPathOnly' => $permission->isCertainPath(),
            ];
        }, $permissions);

        return new GetEditRolesAndPermissionsResponseDTO($rolesData, $permissionsData);
    }
}

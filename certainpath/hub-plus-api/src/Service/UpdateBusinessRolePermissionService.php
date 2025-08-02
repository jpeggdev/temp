<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\Request\UpdateBusinessRolePermissionDTO;
use App\Entity\BusinessRolePermission;
use App\Exception\BusinessRoleNotFoundException;
use App\Exception\PermissionNotFoundException;
use App\Repository\BusinessRolePermissionRepository;
use App\Repository\BusinessRoleRepository;
use App\Repository\PermissionRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateBusinessRolePermissionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BusinessRoleRepository $businessRoleRepository,
        private PermissionRepository $permissionRepository,
        private BusinessRolePermissionRepository $businessRolePermissionRepository,
    ) {
    }

    public function updateBusinessRolePermissions(UpdateBusinessRolePermissionDTO $dto): void
    {
        $role = $this->businessRoleRepository->find($dto->roleId);

        if (!$role) {
            throw new BusinessRoleNotFoundException();
        }

        $permissions = $this->permissionRepository->findBy(['id' => $dto->permissionIds]);

        if (count($permissions) !== count($dto->permissionIds)) {
            throw new PermissionNotFoundException('One or more permissions not found.');
        }

        $currentPermissions = $role->getRolePermissions()->toArray();

        $currentPermissionIds = array_map(
            fn (BusinessRolePermission $rp) => $rp->getPermission()->getId(),
            $currentPermissions
        );

        $submittedPermissionIds = $dto->permissionIds;

        $permissionsToAddIds = array_diff($submittedPermissionIds, $currentPermissionIds);
        $permissionsToRemoveIds = array_diff($currentPermissionIds, $submittedPermissionIds);

        foreach ($permissionsToAddIds as $permissionId) {
            $permission = $this->permissionRepository->find($permissionId);
            if ($permission) {
                $rolePermission = new BusinessRolePermission();
                $rolePermission->setRole($role);
                $rolePermission->setPermission($permission);

                $this->entityManager->persist($rolePermission);
            }
        }

        foreach ($permissionsToRemoveIds as $permissionId) {
            $rolePermission = $this->businessRolePermissionRepository->findOneBy([
                'role' => $role,
                'permission' => $permissionId,
            ]);

            if ($rolePermission) {
                $this->entityManager->remove($rolePermission);
            }
        }

        $this->entityManager->flush();
    }
}

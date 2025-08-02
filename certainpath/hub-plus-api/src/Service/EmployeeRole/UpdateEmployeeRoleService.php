<?php

declare(strict_types=1);

namespace App\Service\EmployeeRole;

use App\DTO\Request\EmployeeRole\CreateUpdateEmployeeRoleDTO;
use App\DTO\Response\EmployeeRole\CreateUpdateEmployeeRoleResponseDTO;
use App\Entity\EmployeeRole;
use App\Exception\CreateUpdateEmployeeRoleException;
use App\Repository\EmployeeRoleRepository;

readonly class UpdateEmployeeRoleService
{
    public function __construct(
        private EmployeeRoleRepository $employeeRoleRepository,
    ) {
    }

    public function updateRole(EmployeeRole $role, CreateUpdateEmployeeRoleDTO $dto): CreateUpdateEmployeeRoleResponseDTO
    {
        $existing = $this->employeeRoleRepository->findOneByName($dto->name);
        if ($existing && $existing->getId() !== $role->getId()) {
            throw new CreateUpdateEmployeeRoleException(sprintf('An EmployeeRole with the name "%s" already exists.', $dto->name));
        }

        $role->setName($dto->name);
        $this->employeeRoleRepository->save($role, true);

        return new CreateUpdateEmployeeRoleResponseDTO(
            id: $role->getId(),
            name: $role->getName(),
        );
    }
}

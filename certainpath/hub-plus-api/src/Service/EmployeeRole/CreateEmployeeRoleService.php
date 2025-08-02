<?php

declare(strict_types=1);

namespace App\Service\EmployeeRole;

use App\DTO\Request\EmployeeRole\CreateUpdateEmployeeRoleDTO;
use App\DTO\Response\EmployeeRole\CreateUpdateEmployeeRoleResponseDTO;
use App\Entity\EmployeeRole;
use App\Exception\CreateUpdateEmployeeRoleException;
use App\Repository\EmployeeRoleRepository;

readonly class CreateEmployeeRoleService
{
    public function __construct(
        private EmployeeRoleRepository $employeeRoleRepository,
    ) {
    }

    public function createRole(CreateUpdateEmployeeRoleDTO $dto): CreateUpdateEmployeeRoleResponseDTO
    {
        $existing = $this->employeeRoleRepository->findOneByName($dto->name);
        if ($existing) {
            throw new CreateUpdateEmployeeRoleException(sprintf('An EmployeeRole with the name "%s" already exists.', $dto->name));
        }

        $role = new EmployeeRole();
        $role->setName($dto->name);

        $this->employeeRoleRepository->save($role, true);

        return new CreateUpdateEmployeeRoleResponseDTO(
            id: $role->getId(),
            name: $role->getName(),
        );
    }
}

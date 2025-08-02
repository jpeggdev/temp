<?php

declare(strict_types=1);

namespace App\Service\EmployeeRole;

use App\DTO\Request\EmployeeRole\GetEmployeeRolesRequestDTO;
use App\DTO\Response\EmployeeRole\GetEmployeeRolesResponseDTO;
use App\Repository\EmployeeRoleRepository;

readonly class GetEmployeeRolesService
{
    public function __construct(
        private EmployeeRoleRepository $employeeRoleRepository,
    ) {
    }

    /**
     * @return array{
     *     roles: GetEmployeeRolesResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getRoles(GetEmployeeRolesRequestDTO $dto): array
    {
        $roles = $this->employeeRoleRepository->findRolesByQuery($dto);
        $totalCount = $this->employeeRoleRepository->countRolesByQuery($dto);
        $roleDtos = GetEmployeeRolesResponseDTO::fromEntities($roles);

        return [
            'roles' => $roleDtos,
            'totalCount' => $totalCount,
        ];
    }
}

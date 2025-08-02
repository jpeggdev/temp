<?php

declare(strict_types=1);

namespace App\Service\EmployeeRole;

use App\DTO\Response\EmployeeRole\GetEditEmployeeRoleResponseDTO;
use App\Entity\EmployeeRole;

readonly class GetEditEmployeeRoleService
{
    public function getEditEmployeeRoleDetails(EmployeeRole $employeeRole): GetEditEmployeeRoleResponseDTO
    {
        return new GetEditEmployeeRoleResponseDTO(
            id: $employeeRole->getId(),
            name: $employeeRole->getName()
        );
    }
}

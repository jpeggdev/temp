<?php

declare(strict_types=1);

namespace App\DTO\Response\EmployeeRole;

use App\Entity\EmployeeRole;

class GetEmployeeRolesResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromEntity(EmployeeRole $employeeRole): self
    {
        return new self(
            id: $employeeRole->getId(),
            name: $employeeRole->getName() ?? ''
        );
    }

    /**
     * Convert an array of EmployeeRole entities into an array of DTOs.
     *
     * @param EmployeeRole[] $employeeRoles
     *
     * @return GetEmployeeRolesResponseDTO[]
     */
    public static function fromEntities(array $employeeRoles): array
    {
        return array_map(
            static fn (EmployeeRole $employeeRole) => self::fromEntity($employeeRole),
            $employeeRoles
        );
    }
}

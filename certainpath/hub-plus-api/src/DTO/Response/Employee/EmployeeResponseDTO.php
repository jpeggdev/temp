<?php

declare(strict_types=1);

namespace App\DTO\Response\Employee;

use App\Entity\Employee;

class EmployeeResponseDTO
{
    public function __construct(
        public int $employeeId,
        public string $companyName,
        public int $companyId,
        public string $intacctId,
        public string $roleName,
    ) {
    }

    public static function fromEmployee(Employee $employee): self
    {
        return new self(
            $employee->getId(),
            $employee->getCompany()->getCompanyName(),
            $employee->getCompany()->getId(),
            $employee->getCompany()->getIntacctId(),
            $employee->getRole()->getInternalName()
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Employee;

use App\DTO\Request\Employee\EditEmployeeDTO;
use App\DTO\Response\Employee\EditEmployeeResponseDTO;
use App\Entity\Employee;
use App\Repository\EmployeeRepository;

readonly class EditEmployeeService
{
    public function __construct(private EmployeeRepository $employeeRepository)
    {
    }

    public function editEmployee(Employee $employee, EditEmployeeDTO $editEmployeeDTO): EditEmployeeResponseDTO
    {
        $employee->setFirstName($editEmployeeDTO->firstName);
        $employee->setLastName($editEmployeeDTO->lastName);

        $this->employeeRepository->save($employee, true);

        return EditEmployeeResponseDTO::fromEntity($employee);
    }
}

<?php

declare(strict_types=1);

namespace App\Service\Employee;

use App\DTO\Request\UserQueryDTO;
use App\DTO\Response\UserListResponseDTO;
use App\Entity\Company;
use App\Repository\EmployeeRepository;

readonly class EmployeeQueryService
{
    public function __construct(private EmployeeRepository $employeeRepository)
    {
    }

    /**
     * @return array{
     *     users: UserListResponseDTO[],
     *     totalCount: int
     * }
     */
    public function getEmployees(UserQueryDTO $queryDto, Company $company): array
    {
        $employees = $this->employeeRepository->findEmployeesByQuery($queryDto, $company);
        $totalCount = $this->employeeRepository->getTotalCount($queryDto, $company);

        $userDtos = array_map(
            fn ($employee) => UserListResponseDTO::fromEntity(
                $employee->getUser(),
                $employee
            ),
            $employees
        );

        return [
            'users' => $userDtos,
            'totalCount' => $totalCount,
        ];
    }
}

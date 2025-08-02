<?php

declare(strict_types=1);

namespace App\Service\Company;

use App\Constants\Permissions;
use App\DTO\LoggedInUserDTO;
use App\DTO\Response\Company\MyCompaniesResponseDTO;
use App\Repository\CompanyRepository;
use App\Service\PermissionService;

class GetMyCompaniesService
{
    private CompanyRepository $companyRepository;
    private PermissionService $permissionService;

    public function __construct(CompanyRepository $companyRepository, PermissionService $permissionService)
    {
        $this->companyRepository = $companyRepository;
        $this->permissionService = $permissionService;
    }

    /**
     * @return array<MyCompaniesResponseDTO>
     */
    public function getMyCompanies(
        LoggedInUserDTO $loggedInUserDTO,
        int $page = 1,
        int $limit = 100,
        ?string $search = null,
    ): array {
        $user = $loggedInUserDTO->getUser();
        $activeEmployee = $loggedInUserDTO->getActiveEmployee();

        $employeeCompanyIds = array_map(function ($employee) {
            return $employee->getCompany()->getId();
        }, $user->getEmployeeRecords()->toArray());

        $marketingEnabled = null;
        $includeAllCompanies = false;

        if ($this->permissionService->hasPermission($activeEmployee, Permissions::CAN_SWITCH_COMPANY_ALL)) {
            $includeAllCompanies = true;
        } elseif ($this->permissionService->hasPermission($activeEmployee, Permissions::CAN_SWITCH_COMPANY_MARKETING_ONLY)) {
            $marketingEnabled = true;
        }

        $companies = $this->companyRepository->findAllCompaniesWithEmployeeRelatedQueryBuilder(
            $employeeCompanyIds,
            $page,
            $limit,
            $includeAllCompanies,
            $search,
            $marketingEnabled
        );

        return MyCompaniesResponseDTO::fromCompanyCollection($companies);
    }
}

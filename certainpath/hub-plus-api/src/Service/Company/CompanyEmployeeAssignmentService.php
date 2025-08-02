<?php

namespace App\Service\Company;

use App\DTO\Request\Employee\CreateEmployeeDTO;
use App\Entity\BusinessRole;
use App\Entity\Company;
use App\Entity\Employee;
use App\Repository\BusinessRoleRepository;
use App\Repository\EmployeeRepository;
use App\Repository\UserRepository;
use App\Service\ApplicationSignalingService;
use App\Service\Employee\CreateEmployeeService;
use App\ValueObject\Roster\RosterEmployee;
use App\ValueObject\Roster\RosterRole;
use Symfony\Component\Console\Output\OutputInterface;

readonly class CompanyEmployeeAssignmentService
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private CreateEmployeeService $createEmployeeService,
        private UserRepository $userRepository,
        private BusinessRoleRepository $businessRoleRepository,
        private ApplicationSignalingService $signal,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function assignEmployeeToCompany(
        Company $company,
        string $employeeEmail,
        string $employeeFirstName,
        string $employeeLastName,
    ): ?Employee {
        $existingUser = $this->userRepository->findOneByEmail(
            $employeeEmail,
        );
        if ($existingUser) {
            $this->signal->console(
                'Found Existing User: '
                .$existingUser->getEmail()
                .' / '
                .$existingUser->getFirstName()
                .' / '
                .$existingUser->getLastName()
                .' / Against Salesforce Employee: '
                .$employeeEmail
                .' / '
                .$employeeFirstName
                .' / '
                .$employeeLastName
            );
        }
        $employeeToSave = $existingUser ? $this->employeeRepository->findEmployeeForCompany(
            $existingUser,
            $company->getUuid()
        ) : null;
        if ($employeeToSave) {
            $this->signal->console(
                'Found Existing Employee: '
                .$employeeToSave->getFirstName()
                .' / '
                .$employeeToSave->getLastName()
                .' / User: E-Mail: '
                .$employeeToSave->getUser()->getEmail()
            );
        }
        if (!$employeeToSave) {
            $createEmployeeDTO = new CreateEmployeeDTO(
                $employeeFirstName,
                $employeeLastName,
                $employeeEmail,
            );
            $this->signal->console(
                'BEGIN CREATE: '
                .$company->getIntacctId()
                .': '
                .$employeeFirstName
                .' / '
                .$employeeLastName
                .' / '
                .$employeeEmail
            );
            $dto = $this->createEmployeeService->createEmployee(
                $createEmployeeDTO,
                $company,
            );
            $this->signal->console(
                'SUCCESS CREATE: '
                .$company->getIntacctId()
                .': '
                .$employeeFirstName
                .' / '
                .$employeeLastName
                .' / '
                .$employeeEmail
                .' / '
                .$dto->id
                .' / '
                .$dto->email
                .' / '
                .$dto->firstName
                .' / '
                .$dto->lastName
            );
            $employeeToSave = $this->employeeRepository->findEmployeeByUuid(
                $dto->employeeUuid
            );
        }

        return $employeeToSave;
    }

    /**
     * @param RosterEmployee[] $salesforceEmployees
     *
     * @throws \Exception
     */
    public function assignSalesforceEmployeesToCompany(
        array $salesforceEmployees,
        Company $company,
    ): void {
        foreach ($salesforceEmployees as $salesforceEmployee) {
            if (!$salesforceEmployee->hasEmail()) {
                continue;
            }
            $savedEmployee = $this->assignEmployeeToCompany(
                $company,
                $salesforceEmployee->getEmail(),
                $salesforceEmployee->getFirstName(),
                $salesforceEmployee->getLastName(),
            );
            $this->signal->console(
                'Employee Before Sync: '
                .$savedEmployee?->getFirstName()
                .' / '
                .$savedEmployee?->getLastName()
                .' / '
                .$savedEmployee?->getId()
                .' / User: E-Mail: '
                .$savedEmployee?->getUser()?->getEmail()
                .' / '
                .$savedEmployee?->getUser()?->getFirstName()
                .' / '
                .$savedEmployee?->getUser()?->getLastName()
            );
            if ($savedEmployee) {
                $targetRole = $this->businessRoleRepository->getRole(
                    BusinessRole::fromRosterRole(
                        RosterRole::fromRosterEmployee(
                            $salesforceEmployee,
                        )
                    )
                );
                $savedEmployee->updateFromSalesforceEmployeeRecord(
                    $salesforceEmployee,
                    $targetRole
                );
                $this->employeeRepository->saveEmployee(
                    $savedEmployee,
                );
            }
            $this->signal->console(
                'Employee After Sync: '
                .$savedEmployee?->getFirstName()
                .' / '
                .$savedEmployee?->getLastName()
                .' / '
                .$savedEmployee?->getId()
                .' / User: E-Mail: '
                .$savedEmployee?->getUser()?->getEmail()
                .' / '
                .$savedEmployee?->getUser()?->getFirstName()
                .' / '
                .$savedEmployee?->getUser()?->getLastName()
            );
        }
    }

    public function setSignaling(OutputInterface $output): void
    {
        $this->signal->setOutput(
            $output
        );
        $this->createEmployeeService->setSignaling(
            $output
        );
    }
}

<?php

namespace App\Service\Company;

use App\Entity\BusinessRole;
use App\Entity\Company;
use App\Repository\BusinessRoleRepository;
use App\Repository\EmployeeRepository;
use App\Service\ApplicationSignalingService;
use App\ValueObject\Roster\RosterCoach;
use Symfony\Component\Console\Output\OutputInterface;

readonly class CompanyCoachAssignmentService
{
    public function __construct(
        private EmployeeRepository $employeeRepository,
        private CompanyEmployeeAssignmentService $companyEmployeeAssignmentService,
        private BusinessRoleRepository $businessRoleRepository,
        private ApplicationSignalingService $signal,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function assignSalesforceCoachToCompany(
        Company $companyToSave,
        ?RosterCoach $salesForceCoach,
    ): void {
        if ($salesForceCoach) {
            $employeeEmail = $salesForceCoach->getEmail();
            $employeeFirstName = $salesForceCoach->getFirstName();
            $employeeLastName = $salesForceCoach->getLastName();
            $employeeToSave = $this->companyEmployeeAssignmentService->assignEmployeeToCompany(
                $companyToSave,
                $employeeEmail,
                $employeeFirstName,
                $employeeLastName
            );
            if ($employeeToSave) {
                $employeeToSave->updateFromSalesforceCoachRecord(
                    $salesForceCoach,
                    $this->businessRoleRepository->getRole(
                        BusinessRole::coach()
                    )
                );
                $this->employeeRepository->saveEmployee(
                    $employeeToSave,
                );
            }
        } else {
            $this->signal->console(
                'No Coach for: '.$companyToSave->getIntacctId()
            );
        }
    }

    public function setSignaling(OutputInterface $output): void
    {
        $this->signal->setOutput(
            $output
        );
        $this->companyEmployeeAssignmentService->setSignaling(
            $output
        );
    }
}

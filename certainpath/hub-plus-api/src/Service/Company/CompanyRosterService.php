<?php

namespace App\Service\Company;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Service\ApplicationSignalingService;
use App\ValueObject\Roster\RosterCompany;
use Symfony\Component\Console\Output\OutputInterface;

readonly class CompanyRosterService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private CompanyCoachAssignmentService $companyCoachAssignmentService,
        private CompanyEmployeeAssignmentService $companyEmployeeAssignmentService,
        private ApplicationSignalingService $signal,
    ) {
    }

    /**
     * @param RosterCompany[] $rosterCompanies
     *
     * @return Company[]
     *
     * @throws \Exception
     */
    public function createOrUpdateCompaniesRosters(
        array $rosterCompanies,
    ): array {
        /** @var Company[] $updatedCompanies */
        $updatedCompanies = [];
        $totalCount = count($rosterCompanies);
        $count = 1;
        foreach ($rosterCompanies as $rosterCompany) {
            $this->signal->console(
                'Processing: '
                .$count.'/'.$totalCount.': '
                .$rosterCompany->getIntacctId()
                .' / '.$rosterCompany->getName()
            );
            $updatedCompanies[] = $this->createOrUpdateCompanyRoster(
                $rosterCompany,
            );
            ++$count;
        }

        return $updatedCompanies;
    }

    /**
     * @throws \Exception
     */
    public function createOrUpdateCompanyRoster(RosterCompany $salesforceCompany): Company
    {
        $existingCompany = $this->companyRepository->findOneByIdentifier(
            $salesforceCompany->getIntacctId(),
        );
        $companyToSave = $existingCompany ?: new Company();
        $companyToSave->setFieldsFromSalesforceRecord(
            $salesforceCompany
        );
        $this->companyRepository->saveCompany(
            $companyToSave
        );
        $salesForceCoach = $salesforceCompany->getCoach();
        $this->signal->console(
            'Assigning Coach for: '.$salesforceCompany->getIntacctId().': '.$salesForceCoach?->getEmail()
        );
        $this->companyCoachAssignmentService->assignSalesforceCoachToCompany(
            $companyToSave,
            $salesForceCoach
        );
        $salesforceEmployees = $salesforceCompany->getEmployees();
        $this->companyEmployeeAssignmentService->assignSalesforceEmployeesToCompany(
            $salesforceEmployees,
            $companyToSave
        );

        return $companyToSave;
    }

    public function setSignaling(OutputInterface $output): void
    {
        $this->signal->setOutput(
            $output
        );
        $this->companyEmployeeAssignmentService->setSignaling(
            $output
        );
        $this->companyCoachAssignmentService->setSignaling(
            $output
        );
    }
}

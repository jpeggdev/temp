<?php

namespace App\Tests\Service\Company;

use App\DTO\Request\Company\CompanyQueryDTO;
use App\DTO\Request\Company\CreateCompanyDTO;
use App\Entity\BusinessRole;
use App\Entity\Company;
use App\Entity\Employee;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\Roster\RosterCompany;
use App\ValueObject\Roster\RosterEmployee;
use App\ValueObject\Roster\RosterRole;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CompanyRosterServiceTest extends AbstractKernelTestCase
{
    public function setUp(): void
    {
        $this->doInitializeBusinessRoles = true;
        parent::setUp();
    }
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testRosterService(): void
    {
        $rosterService = $this->getCompanyRosterService();
        self::assertNotNull($rosterService);
        $queryService = $this->getCompanyQueryService();
        self::assertNotNull($queryService);
        $createService = $this->getCreateCompanyService();
        self::assertNotNull($createService);
        $editService = $this->getEditCompanyService();
        self::assertNotNull($editService);

        $queryDTO = new CompanyQueryDTO();

        $companiesResultset = $queryService->getCompanies(
            $queryDTO
        );

        self::assertEmpty($companiesResultset['companies']);

        $salesforceCompanies = $this
            ->getSalesforceRosterService()
            ->getCompanies(10);
        $testSalesforceCompanyOne = $salesforceCompanies[9]; // 99 156
        self::assertCount(4, $testSalesforceCompanyOne->getEmployees()); // 2 3
        self::assertNotNull($testSalesforceCompanyOne);

        self::assertNotNull($testSalesforceCompanyOne->getPrimaryMemberEmail());
        self::assertNotNull($testSalesforceCompanyOne->getIntacctContactEmail());

        $createCompanyDTO = new CreateCompanyDTO(
            companyName: $testSalesforceCompanyOne->getName(),
            websiteUrl: $testSalesforceCompanyOne->getWebsite(),
            salesforceId: $testSalesforceCompanyOne->getSalesforceId(),
            intacctId: $testSalesforceCompanyOne->getIntacctId(),
            companyEmail: $testSalesforceCompanyOne->getPrimaryMemberEmail()
        );

        $createService->createCompany($createCompanyDTO);
        $companiesResultset = $queryService->getCompanies(new CompanyQueryDTO());
        self::assertCount(1, $companiesResultset['companies']);
        /** @var Company $firstCompany */
        $firstCompany =
            $this->companyRepository->getCompanyFromDTO(
                $companiesResultset['companies'][0]
            );
        self::assertNull($firstCompany->getAddressLine1());
        $this->assertCompanyFieldsAreNull($firstCompany);

        $rosterService->createOrUpdateCompanyRoster(
            $testSalesforceCompanyOne
        );
        $rosterService->createOrUpdateCompanyRoster(
            $testSalesforceCompanyOne
        );

        $companiesResultset = $queryService->getCompanies(new CompanyQueryDTO());
        self::assertCount(1, $companiesResultset['companies']);
        $this->assertMatchingCompanyRecords(
            $testSalesforceCompanyOne,
            $firstCompany
        );

        $testSalesforceCompanyTwo = $salesforceCompanies[8];
        $rosterService->createOrUpdateCompanyRoster($testSalesforceCompanyTwo);
        $rosterService->createOrUpdateCompanyRoster($testSalesforceCompanyTwo);
        $companiesResultset = $queryService->getCompanies(new CompanyQueryDTO());
        self::assertCount(2, $companiesResultset['companies']);
        $secondCompany = $this->companyRepository->getCompanyFromDTO(
            $companiesResultset['companies'][1]
        );
        $this->assertMatchingCompanyRecords(
            $testSalesforceCompanyTwo,
            $secondCompany
        );

        /** @var Employee[] $firstCompanyEmployees */
        $firstCompanyEmployees = $firstCompany->getEmployeeRecords();
        self::assertCount(5, $firstCompanyEmployees); // 3
        $coach = $firstCompanyEmployees[0];
        self::assertTrue(
            $coach->isRole(
                BusinessRole::coach()
            )
        );
        $employeeOne = $firstCompanyEmployees[1];
        self::assertFalse(
            $employeeOne->isRole(
                BusinessRole::coach()
            )
        );
        $this->assertEmployeeFieldsMatch(
            $employeeOne,
            $testSalesforceCompanyOne->getEmployees()[0]
        );
        $employeeTwo = $firstCompanyEmployees[2];
        self::assertFalse(
            $employeeTwo->isRole(
                BusinessRole::coach()
            )
        );
        $this->assertEmployeeFieldsMatch(
            $employeeTwo,
            $testSalesforceCompanyOne->getEmployees()[1]
        );
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testUpdateAllCompanies(): void
    {
        $salesforceCoaches = [];
        $salesforceEmployees = [];
        $salesforceCompanies = $this
            ->getSalesforceRosterService()
            ->getCompanies(5);
        foreach ($salesforceCompanies as $salesforceCompany) {
            $coachId = $salesforceCompany->getCoach()->getSalesforceId();
            $companyId = $salesforceCompany->getSalesforceId();
            $coachCompanyKey = $coachId.'-'.$companyId;
            if (!in_array($coachCompanyKey, $salesforceCoaches, true)) {
                $salesforceCoaches[] = $coachCompanyKey;
            }
            foreach ($salesforceCompany->getEmployees() as $employee) {
                if ($employee->hasEmail()) {
                    $salesforceEmployees[] = $employee;
                }
            }
        }
        $countOfSalesforceCoaches = count($salesforceCoaches);
        $countOfSalesforceEmployees = count($salesforceEmployees);
        $countOfSalesforceCompanies = count($salesforceCompanies);

        $service = $this->getCompanyRosterService();

        $companies = $service->createOrUpdateCompaniesRosters(
            $salesforceCompanies
        );
        self::assertCount(
            $countOfSalesforceCompanies,
            $companies
        );
        $coachRole = $this->businessRoleRepository->getRole(
            BusinessRole::coach()
        );
        $allCoaches = $this->employeeRepository->getEmployeesMatchingRole(
            $coachRole
        );
        self::assertCount(
            $countOfSalesforceCoaches,
            $allCoaches
        );
        $companyEmployees = $this->employeeRepository->getEmployeesMatchingRoles(
            $this->getCompanyEmployeeRoles()
        );
        self::assertCount(
            $countOfSalesforceEmployees,
            $companyEmployees
        );
        //        $this->debugString($countOfSalesforceCoaches);
        //        $this->debugString($countOfSalesforceEmployees);
        //        $this->debugString($countOfSalesforceCompanies);
    }

    private function assertCompanyFieldsAreNull(Company $company): void
    {
        self::assertNull(
            $company->getAddressLine1()
        );
        self::assertNull(
            $company->getCity()
        );
        self::assertNull(
            $company->getState()
        );
        self::assertNull(
            $company->getZipCode()
        );
        self::assertNotNull(
            $company->getCountry()
        );
        self::assertNull(
            $company->getMailingAddressLine1()
        );
        self::assertNull(
            $company->getMailingState()
        );
        self::assertNull(
            $company->getMailingZipCode()
        );
        self::assertNotNull(
            $company->getMailingCountry()
        );
    }

    private function assertCompanyFieldsNotNull(Company $company): void
    {
        self::assertNotNull(
            $company->getAddressLine1()
        );
        self::assertNotNull(
            $company->getCity()
        );
        self::assertNotNull(
            $company->getState()
        );
        self::assertNotNull(
            $company->getZipCode()
        );
        self::assertNotNull(
            $company->getCountry()
        );
        self::assertNotNull(
            $company->getMailingAddressLine1()
        );
        self::assertNotNull(
            $company->getMailingState()
        );
        self::assertNotNull(
            $company->getMailingZipCode()
        );
        self::assertNotNull(
            $company->getMailingCountry()
        );
    }

    private function assertMatchingCompanyRecords(
        RosterCompany $salesforceCompany,
        Company $company,
    ): void {
        if ($salesforceCompany->isStochasticActive()) {
            self::assertTrue(
                $company->isMarketingEnabled()
            );
        }
        self::assertSame(
            $salesforceCompany->getName(),
            $company->getCompanyName()
        );
        self::assertSame(
            $salesforceCompany->getWebsite(),
            $company->getWebsiteUrl()
        );
        self::assertSame(
            $salesforceCompany->getSalesforceId(),
            $company->getSalesforceId()
        );
        self::assertSame(
            $salesforceCompany->getIntacctId(),
            $company->getIntacctId()
        );
        self::assertSame(
            $salesforceCompany->getPrimaryMemberEmail(),
            $company->getCompanyEmail()
        );
        $this->assertCompanyFieldsNotNull($company);
        self::assertSame(
            $salesforceCompany->getBillingStreet(),
            $company->getAddressLine1()
        );
        self::assertSame(
            $salesforceCompany->getBillingCity(),
            $company->getCity()
        );
        self::assertSame(
            $salesforceCompany->getBillingState(),
            $company->getState()
        );
        self::assertSame(
            $salesforceCompany->getBillingPostalCode(),
            $company->getZipCode()
        );
        self::assertSame(
            $salesforceCompany->getBillingCountry(),
            $company->getCountry()
        );
        self::assertSame(
            $salesforceCompany->getShippingStreet(),
            $company->getMailingAddressLine1()
        );
        self::assertSame(
            $salesforceCompany->getShippingCity(),
            $company->getCity()
        );
        self::assertSame(
            $salesforceCompany->getShippingState(),
            $company->getMailingState()
        );
        self::assertSame(
            $salesforceCompany->getShippingPostalCode(),
            $company->getMailingZipCode()
        );
        self::assertSame(
            $salesforceCompany->getShippingCountry(),
            $company->getMailingCountry()
        );
    }

    private function assertEmployeeFieldsMatch(
        Employee $employee,
        RosterEmployee $salesforceEmployee,
    ): void {
        self::assertSame(
            $salesforceEmployee->getFirstName(),
            $employee->getFirstName()
        );
        self::assertSame(
            $salesforceEmployee->getLastName(),
            $employee->getLastName()
        );
        //        self::assertSame(
        //            $salesforceEmployee->getEmail(),
        //            $employee->getWorkEmail()
        //        );
        self::assertSame(
            BusinessRole::fromRosterRole(RosterRole::fromRosterEmployee(
                $salesforceEmployee
            ))->getInternalName(),
            $employee->getRole()->getInternalName()
        );
    }

    private function getCompanyEmployeeRoles(): array
    {
        return [
            $this->businessRoleRepository->getRole(
                BusinessRole::ownerGm()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::manager()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::HRRecruiting()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::financeBackOffice()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::technician()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::callCenter()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::sales()
            ),
            $this->businessRoleRepository->getRole(
                BusinessRole::marketing()
            ),
        ];
    }
}

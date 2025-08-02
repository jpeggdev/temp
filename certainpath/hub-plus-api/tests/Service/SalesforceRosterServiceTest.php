<?php

namespace App\Tests\Service;

use App\Entity\BusinessRole;
use App\Tests\AbstractKernelTestCase;
use App\ValueObject\Roster\RosterCoach;
use App\ValueObject\Roster\RosterCompany;
use App\ValueObject\Roster\RosterEmployee;
use App\ValueObject\Roster\RosterRole;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class SalesforceRosterServiceTest extends AbstractKernelTestCase
{
    private array $testSalesforceCompany;
    private array $testSalesforceCoach;
    private array $testSalesforceEmployee;

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testUnjoinedCompanyCoachEmployeeRosters(): void
    {
        $client = $this->getSalesforceClient();

        $companies = $client->getCompanies(100);
        $this->testSalesforceCompany = $companies['records'][99];
        $company = RosterCompany::fromSalesforceCompany(
            $this->testSalesforceCompany
        );
        $this->assertCompanyFields($company);

        $coachUserId = $company->getOwnerId();
        $users = $client->getSalesforceUsers();
        $coachUser = null;
        foreach ($users['records'] as $user) {
            if ($user['Id'] === $coachUserId) {
                $coachUser = $user;
                break;
            }
        }
        self::assertNotNull($coachUser);
        $this->testSalesforceCoach = $coachUser;
        $coach = RosterCoach::fromSalesforceCoach(
            $this->testSalesforceCoach
        );
        $this->assertCoachFields($coach);

        $employees = $client->getCompanyEmployees();
        $employeesForCompany = [];
        foreach ($employees['records'] as $employee) {
            if ($employee['AccountId'] === $company->getSalesforceId()) {
                $employeesForCompany[] = $employee;
            }
        }
        self::assertNotEmpty($employeesForCompany);
        self::assertCount(5, $employeesForCompany);
        foreach ($employeesForCompany as $employee) {
            self::assertSame(
                $employee['AccountId'],
                $company->getSalesforceId(),
            );
            self::assertSame(
                $employee['IntacctID_Contact__c'],
                $company->getIntacctId(),
            );
        }
        $this->testSalesforceEmployee = $employeesForCompany[0];
        $employee = RosterEmployee::fromSalesforceEmployee(
            $this->testSalesforceEmployee
        );
        $this->assertEmployeeFields($employee);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testJoinedCompanyRoster(): void
    {
        $client = $this->getSalesforceClient();

        $companies = $client->getCompanies(100);
        $this->testSalesforceCompany = $companies['records'][99];

        $company = RosterCompany::fromSalesforceCompanyCoachEmployees(
            $this->testSalesforceCompany,
            $client->getSalesforceUsers()['records'],
            $client->getCompanyEmployees()['records'],
        );

        $this->assertCompanyFields($company);

        $coach = $company->getCoach();
        self::assertNotNull($coach);
        self::assertSame(
            $company->getOwnerId(),
            $coach->getSalesforceId(),
        );

        $employees = $company->getEmployees();
        self::assertCount(5, $employees);
        foreach ($employees as $employee) {
            self::assertSame(
                $company->getIntacctId(),
                $employee->getIntacctId(),
            );
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testRosterService(): void
    {
        $service = $this->getSalesforceRosterService();
        self::assertNotNull($service);

        $companies = $service->getCompanies(5);
        self::assertGreaterThanOrEqual(
            5,
            count($companies)
        );
        foreach ($companies as $company) {
            self::assertInstanceOf(
                RosterCompany::class,
                $company
            );
            $employees = $company->getEmployees();
            foreach ($employees as $employee) {
                self::assertInstanceOf(
                    RosterEmployee::class,
                    $employee
                );
            }
            $coach = $company->getCoach();
            if (null !== $coach) {
                self::assertInstanceOf(
                    RosterCoach::class,
                    $coach
                );
            }
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testCompanyEmployeeTitleMapping(): void
    {
        $service = $this->getSalesforceRosterService();
        self::assertNotNull($service);

        $employeeTitles = [];
        $normalizedEmployeeTitles = [];
        $stochasticStatuses = [];
        $companies = $service
            ->getCompanies(100);
        foreach ($companies as $company) {
            $status = $company->getStochasticMarketingStatus();
            if (!isset($stochasticStatuses[$status])) {
                $stochasticStatuses[$status] = 1;
            } else {
                ++$stochasticStatuses[$status];
            }
            if (RosterCompany::ACTIVE === $company->getStochasticMarketingStatus()) {
                self::assertTrue($company->isStochasticActive());
            }
            foreach ($company->getEmployees() as $employee) {
                $employeeTitle = $employee->getTitle() ?? 'NO TITLE';
                if (!isset($employeeTitles[$employeeTitle])) {
                    $employeeTitles[$employeeTitle] = 1;
                } else {
                    ++$employeeTitles[$employeeTitle];
                }
                $rosterRole = RosterRole::fromRosterEmployee(
                    $employee
                );
                $businessRole = BusinessRole::fromRosterRole(
                    $rosterRole
                );
                self::assertSame(
                    $businessRole->getInternalName(),
                    $rosterRole->getInternalName(),
                    $employeeTitle
                    .' / '
                    .$employee->getContactType()
                    .' / '
                    .$rosterRole->getInternalName()
                );
                $internalName = $businessRole->getInternalName();
                if (!isset($normalizedEmployeeTitles[$internalName])) {
                    $normalizedEmployeeTitles[$internalName] = 1;
                } else {
                    ++$normalizedEmployeeTitles[$internalName];
                }
            }
        }
        $this->debug(
            $stochasticStatuses
        );
        $this->debug(
            $normalizedEmployeeTitles
        );
        self::assertGreaterThanOrEqual(
            143, // 152
            $normalizedEmployeeTitles[BusinessRole::ROLE_OWNER_GM]
        );
        self::assertGreaterThanOrEqual(
            90, // 103
            $normalizedEmployeeTitles[BusinessRole::ROLE_FINANCE_BACK_OFFICE]
        );
        self::assertGreaterThanOrEqual(
            114, // 123
            $normalizedEmployeeTitles[BusinessRole::ROLE_MANAGER]
        );
        self::assertGreaterThanOrEqual(
            44, // 44 50
            $normalizedEmployeeTitles[BusinessRole::ROLE_CALL_CENTER]
        );
        self::assertGreaterThanOrEqual(
            55, // 63
            $normalizedEmployeeTitles[BusinessRole::ROLE_TECHNICIAN]
        );
        self::assertGreaterThanOrEqual(
            74, // 80
            $normalizedEmployeeTitles[BusinessRole::ROLE_SALES]
        );
        self::assertGreaterThanOrEqual(
            13,
            $normalizedEmployeeTitles[BusinessRole::ROLE_HR_RECRUITING]
        );
    }

    private function assertEmployeeField(
        string $field,
        mixed $value,
    ): void {
        self::assertSame(
            $this->testSalesforceEmployee[$field],
            $value,
        );
    }

    private function assertEmployeeFields(
        RosterEmployee $employee,
    ): void {
        $this->assertEmployeeField(
            'Id',
            $employee->getSalesforceId(),
        );
        $this->assertEmployeeField(
            'FirstName',
            $employee->getFirstName(),
        );
        $this->assertEmployeeField(
            'LastName',
            $employee->getLastName(),
        );
        $this->assertEmployeeField(
            'Email',
            $employee->getEmail(),
        );
        $this->assertEmployeeField(
            'Phone',
            $employee->getPhone(),
        );
        $this->assertEmployeeField(
            'Title',
            $employee->getTitle(),
        );
        $this->assertEmployeeField(
            'Contact_Type__c',
            $employee->getContactType(),
        );
        $this->assertEmployeeField(
            'Account_status__c',
            $employee->getAccountStatus(),
        );
        $this->assertEmployeeField(
            'Inactive_Contact__c',
            $employee->isInactive(),
        );
        $this->assertEmployeeField(
            'SSO_ID__c',
            $employee->getSsoId(),
        );
        $this->assertEmployeeField(
            'HUB_Account_Suspended__c',
            $employee->isHubAccountSuspended(),
        );
        $this->assertEmployeeField(
            'HUB_User_Type__c',
            $employee->getHubUserType(),
        );
        $this->assertEmployeeField(
            'HUB_Account__c',
            $employee->isHubAccount(),
        );
        $this->assertEmployeeField(
            'IntacctID_Contact__c',
            $employee->getIntacctId(),
        );
        $this->assertEmployeeField(
            'ReportsToId',
            $employee->getReportsToId(),
        );
    }

    private function assertCoachField(
        string $field,
        mixed $value,
    ): void {
        self::assertSame(
            $this->testSalesforceCoach[$field],
            $value,
        );
    }

    private function assertCoachFields(RosterCoach $coach): void
    {
        $this->assertCoachField(
            'Id',
            $coach->getSalesforceId(),
        );
        $this->assertCoachField(
            'CompanyName',
            $coach->getCompanyName(),
        );
        $this->assertCoachField(
            'Department',
            $coach->getDepartment(),
        );
        $this->assertCoachField(
            'Title',
            $coach->getTitle(),
        );
        $this->assertCoachField(
            'Username',
            $coach->getUsername(),
        );
        $this->assertCoachField(
            'Name',
            $coach->getName(),
        );
        $this->assertCoachField(
            'FirstName',
            $coach->getFirstName(),
        );
        $this->assertCoachField(
            'LastName',
            $coach->getLastName(),
        );
        $this->assertCoachField(
            'Phone',
            $coach->getPhone(),
        );
        $this->assertCoachField(
            'Email',
            $coach->getEmail(),
        );
    }

    private function assertCompanyField(
        string $field,
        mixed $value,
    ): void {
        self::assertSame(
            $this->testSalesforceCompany[$field],
            $value,
        );
    }

    private function assertCompanyFields(RosterCompany $company): void
    {
        $this->assertCompanyField(
            'Id',
            $company->getSalesforceId(),
        );
        $this->assertCompanyField(
            'OwnerId',
            $company->getOwnerId()
        );
        $this->assertCompanyField(
            'IntacctID__c',
            $company->getIntacctId()
        );
        $this->assertCompanyField(
            'Name',
            $company->getName()
        );
        $this->assertCompanyField(
            'Account_Status__c',
            $company->getAccountStatus()
        );
        $this->assertCompanyField(
            'Primary_Member__c',
            $company->getPrimaryMemberName()
        );
        $this->assertCompanyField(
            'Primary_Member_Email__c',
            $company->getPrimaryMemberEmail()
        );
        $this->assertCompanyField(
            'Intacct_Contact_Email__c',
            $company->getIntacctContactEmail()
        );
        $this->assertCompanyField(
            'Intacct_Contact_First_Name__c',
            $company->getIntacctContactFirstName()
        );
        $this->assertCompanyField(
            'Intacct_Contact_Last_Name__c',
            $company->getIntacctContactLastName()
        );
        $this->assertCompanyField(
            'Software_Subscription__c',
            $company->hasSoftware()
        );
        $this->assertCompanyField(
            'Stochastic_Marketing_Status__c',
            $company->getStochasticMarketingStatus()
        );
        $this->assertCompanyField(
            'BillingStreet',
            $company->getBillingStreet()
        );
        $this->assertCompanyField(
            'BillingCity',
            $company->getBillingCity()
        );
        $this->assertCompanyField(
            'BillingState',
            $company->getBillingState()
        );
        $this->assertCompanyField(
            'BillingPostalCode',
            $company->getBillingPostalCode()
        );
        $this->assertCompanyField(
            'BillingCountry',
            $company->getBillingCountry()
        );
        $this->assertCompanyField(
            'BillingStateCode',
            $company->getBillingStateCode()
        );
        $this->assertCompanyField(
            'BillingCountryCode',
            $company->getBillingCountryCode()
        );
        $this->assertCompanyField(
            'ShippingStreet',
            $company->getShippingStreet()
        );
        $this->assertCompanyField(
            'ShippingCity',
            $company->getShippingCity()
        );
        $this->assertCompanyField(
            'ShippingState',
            $company->getShippingState()
        );
        $this->assertCompanyField(
            'ShippingPostalCode',
            $company->getShippingPostalCode()
        );
        $this->assertCompanyField(
            'ShippingCountry',
            $company->getShippingCountry()
        );
        $this->assertCompanyField(
            'ShippingStateCode',
            $company->getShippingStateCode()
        );
        $this->assertCompanyField(
            'ShippingCountryCode',
            $company->getShippingCountryCode()
        );
        $this->assertCompanyField(
            'Website',
            $company->getWebsite()
        );
    }
}

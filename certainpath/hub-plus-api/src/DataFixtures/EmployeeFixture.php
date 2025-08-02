<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Constants\ApplicationConstants;
use App\Entity\Application;
use App\Entity\ApplicationAccess;
use App\Entity\BusinessRole;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class EmployeeFixture extends Fixture implements DependentFixtureInterface
{
    public const string EMPLOYEE_REFERENCE_1 = 'employee_1';
    public const string EMPLOYEE_REFERENCE_2 = 'employee_2';
    public const string EMPLOYEE_REFERENCE_3 = 'employee_3';
    public const string EMPLOYEE_REFERENCE_4 = 'employee_4';
    public const string EMPLOYEE_REFERENCE_5 = 'employee_5';
    public const string EMPLOYEE_REFERENCE_6 = 'employee_6';
    public const string EMPLOYEE_REFERENCE_7 = 'employee_7';
    public const string EMPLOYEE_REFERENCE_8 = 'employee_8';
    public const string EMPLOYEE_REFERENCE_9 = 'employee_9';

    public function load(ObjectManager $manager): void
    {
        // Fetch the "ROLE_SUPER_ADMIN" role from the DB by its internalName
        $superAdminRole = $manager->getRepository(BusinessRole::class)
            ->findOneBy(['internalName' => 'ROLE_SUPER_ADMIN']);

        if (!$superAdminRole) {
            throw new \RuntimeException('Could not find BusinessRole with internalName "ROLE_SUPER_ADMIN".');
        }

        /**
         * Create the first employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_1,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE,
            Company::class
        );

        $employee1 = new Employee();
        $employee1->setUser($user);
        $employee1->setFirstName($user->getFirstName());
        $employee1->setLastName($user->getLastName());
        $employee1->setCompany($company);
        $employee1->setRole($superAdminRole);
        $employee1->setUuid(Uuid::uuid4()->toString());
        $manager->persist($employee1);

        $this->addReference(self::EMPLOYEE_REFERENCE_1, $employee1);

        /**
         * Create the second employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_2,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE_1,
            Company::class
        );

        $employee2 = new Employee();
        $employee2->setUser($user);
        $employee2->setFirstName($user->getFirstName());
        $employee2->setLastName($user->getLastName());
        $employee2->setCompany($company);
        $employee2->setRole($superAdminRole);
        $employee2->setUuid(Uuid::uuid4()->toString());
        $manager->persist($employee2);

        $this->addReference(self::EMPLOYEE_REFERENCE_2, $employee2);

        /**
         * Create the third employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_3,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE_2,
            Company::class
        );

        $employee3 = new Employee();
        $employee3->setUser($user);
        $employee3->setFirstName($user->getFirstName());
        $employee3->setLastName($user->getLastName());
        $employee3->setCompany($company);
        $employee3->setRole($superAdminRole);
        $employee3->setUuid(Uuid::uuid4()->toString());
        $manager->persist($employee3);

        $this->addReference(self::EMPLOYEE_REFERENCE_3, $employee3);

        /**
         * Create the fourth employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_4,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE_2,
            Company::class
        );

        $employee4 = new Employee();
        $employee4->setUser($user);
        $employee4->setFirstName($user->getFirstName());
        $employee4->setLastName($user->getLastName());
        $employee4->setCompany($company);
        $employee4->setRole($superAdminRole);
        $manager->persist($employee4);

        $this->addReference(self::EMPLOYEE_REFERENCE_4, $employee4);

        /**
         * Create the fifth employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_5,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE_2,
            Company::class
        );

        $employee5 = new Employee();
        $employee5->setUser($user);
        $employee5->setFirstName($user->getFirstName());
        $employee5->setLastName($user->getLastName());
        $employee5->setCompany($company);
        $employee5->setRole($superAdminRole);
        $manager->persist($employee5);

        $this->addReference(self::EMPLOYEE_REFERENCE_5, $employee5);

        /**
         * Create the sixth employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_6,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE_2,
            Company::class
        );

        $employee6 = new Employee();
        $employee6->setUser($user);
        $employee6->setFirstName($user->getFirstName());
        $employee6->setLastName($user->getLastName());
        $employee6->setCompany($company);
        $employee6->setRole($superAdminRole);
        $manager->persist($employee6);

        $this->addReference(self::EMPLOYEE_REFERENCE_6, $employee6);

        /**
         * Create the seventh employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_7,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE,
            Company::class
        );

        $employee7 = new Employee();
        $employee7->setUser($user);
        $employee7->setFirstName($user->getFirstName());
        $employee7->setLastName($user->getLastName());
        $employee7->setCompany($company);
        $employee7->setRole($superAdminRole);
        $manager->persist($employee7);

        $this->addReference(self::EMPLOYEE_REFERENCE_7, $employee7);

        /**
         * Create the eighth employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_8,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE_2,
            Company::class
        );

        $employee8 = new Employee();
        $employee8->setUser($user);
        $employee8->setFirstName($user->getFirstName());
        $employee8->setLastName($user->getLastName());
        $employee8->setCompany($company);
        $employee8->setRole($superAdminRole);
        $manager->persist($employee8);

        $this->addReference(self::EMPLOYEE_REFERENCE_8, $employee8);

        /**
         * Create the ninth employee.
         */
        $user = $this->getReference(
            UserFixture::USER_REFERENCE_9,
            User::class
        );
        $company = $this->getReference(
            CompanyFixture::COMPANY_REFERENCE,
            Company::class
        );

        $employee9 = new Employee();
        $employee9->setUser($user);
        $employee9->setFirstName($user->getFirstName());
        $employee9->setLastName($user->getLastName());
        $employee9->setCompany($company);
        $employee9->setRole($superAdminRole);
        $employee9->setUuid(Uuid::uuid4()->toString());
        $manager->persist($employee9);

        $this->addReference(self::EMPLOYEE_REFERENCE_9, $employee9);

        // Save all employee entities
        $manager->flush();

        // Assign application access to all employees
        $this->assignApplicationAccess($manager, $employee1);
        $this->assignApplicationAccess($manager, $employee2);
        $this->assignApplicationAccess($manager, $employee3);
        $this->assignApplicationAccess($manager, $employee4);
        $this->assignApplicationAccess($manager, $employee5);
        $this->assignApplicationAccess($manager, $employee6);
        $this->assignApplicationAccess($manager, $employee7);
        $this->assignApplicationAccess($manager, $employee8);
        $this->assignApplicationAccess($manager, $employee9);
    }

    /**
     * For each employee, load Application entities from the DB by name
     * and then create the corresponding ApplicationAccess records.
     */
    private function assignApplicationAccess(ObjectManager $manager, Employee $employee): void
    {
        $applicationNames = [
            ApplicationConstants::HUB,
            ApplicationConstants::EVENT_REGISTRATION,
            ApplicationConstants::STOCHASTIC,
            ApplicationConstants::PARTNER_NETWORK,
            ApplicationConstants::SCOREBOARD,
        ];

        foreach ($applicationNames as $appName) {
            /** @var Application|null $application */
            $application = $manager->getRepository(Application::class)
                ->findOneBy(['name' => $appName]);

            if (!$application) {
                throw new \RuntimeException(sprintf('Could not find Application entity with name "%s".', $appName));
            }

            $applicationAccess = new ApplicationAccess();
            $applicationAccess->setEmployee($employee)
                ->setApplication($application);

            $manager->persist($applicationAccess);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
            CompanyFixture::class,
        ];
    }
}

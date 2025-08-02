<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\PaymentProfile;
use App\Entity\User;
use App\Repository\PaymentProfileRepository;
use App\Tests\AbstractKernelTestCase;

class PaymentProfileRepositoryTest extends AbstractKernelTestCase
{
    private PaymentProfileRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var PaymentProfileRepository $profileRepository */
        $profileRepository = $this->entityManager->getRepository(PaymentProfile::class);
        $this->repository = $profileRepository;
    }

    private function createCompany(): Company
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);
        $this->entityManager->flush();

        return $company;
    }

    private function createEmployee(Company $company): Employee
    {
        $user = new User();
        $user->setEmail($this->faker->email());
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $this->entityManager->persist($user);

        $employee = new Employee();
        $employee->setUser($user);
        $employee->setCompany($company);
        $employee->setFirstName($user->getFirstName());
        $employee->setLastName($user->getLastName());
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $employee;
    }

    private function createPaymentProfile(
        Employee $employee,
        ?string $customerId = null,
        ?string $profileId = null,
    ): PaymentProfile {
        $paymentProfile = new PaymentProfile();
        $paymentProfile->setEmployee($employee);
        $paymentProfile->setAuthnetCustomerId($customerId ?? 'cust_'.$this->faker->randomNumber(8));
        $paymentProfile->setAuthnetPaymentProfileId($profileId ?? 'prof_'.$this->faker->randomNumber(8));
        $paymentProfile->setCardLast4($this->faker->numerify('####'));
        $paymentProfile->setCardType($this->faker->randomElement(['Visa', 'MasterCard', 'Amex', 'Discover']));

        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $paymentProfile;
    }

    public function testFindOneByEmployeeAndAuthNetProfiles(): void
    {
        $company = $this->createCompany();
        $employee = $this->createEmployee($company);

        $customerId = 'customer_12345';
        $profileId = 'payment_67890';
        $paymentProfile = $this->createPaymentProfile($employee, $customerId, $profileId);

        $found = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            $customerId,
            $profileId
        );

        self::assertNotNull($found);
        self::assertSame($paymentProfile->getId(), $found->getId());
        self::assertSame($customerId, $found->getAuthnetCustomerId());
        self::assertSame($profileId, $found->getAuthnetPaymentProfileId());
    }

    public function testFindOneByEmployeeAndAuthNetProfilesReturnsNullWhenNotFound(): void
    {
        $company = $this->createCompany();
        $employee = $this->createEmployee($company);

        $customerId = 'customer_12345';
        $profileId = 'payment_67890';
        $this->createPaymentProfile($employee, $customerId, $profileId);

        $notFound = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            'non_existent_customer',
            $profileId
        );
        self::assertNull($notFound);

        $notFound2 = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            $customerId,
            'non_existent_profile'
        );
        self::assertNull($notFound2);
    }

    public function testFindOneByEmployeeAndAuthNetProfilesFiltersByAllCriteria(): void
    {
        $company1 = $this->createCompany();
        $employee1 = $this->createEmployee($company1);
        $employee2 = $this->createEmployee($company1);

        $customerId = 'customer_12345';
        $profileId = 'payment_67890';

        $profile1 = $this->createPaymentProfile($employee1, $customerId, $profileId);

        $profile2 = $this->createPaymentProfile($employee2, $customerId, 'different_payment_id');

        $profile3 = $this->createPaymentProfile($employee2, 'different_customer_id', $profileId);

        $found = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee1,
            $customerId,
            $profileId
        );
        self::assertNotNull($found);
        self::assertSame($profile1->getId(), $found->getId());

        $found2 = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee2,
            $customerId,
            'different_payment_id'
        );
        self::assertNotNull($found2);
        self::assertSame($profile2->getId(), $found2->getId());

        $found3 = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee2,
            'different_customer_id',
            $profileId
        );
        self::assertNotNull($found3);
        self::assertSame($profile3->getId(), $found3->getId());
    }

    public function testMultiplePaymentProfilesForSameEmployee(): void
    {
        $company = $this->createCompany();
        $employee = $this->createEmployee($company);

        $profile1 = $this->createPaymentProfile($employee, 'cust_1', 'prof_1');
        $profile2 = $this->createPaymentProfile($employee, 'cust_1', 'prof_2');
        $profile3 = $this->createPaymentProfile($employee, 'cust_2', 'prof_1');

        $found1 = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            'cust_1',
            'prof_1'
        );
        self::assertNotNull($found1);
        self::assertSame($profile1->getId(), $found1->getId());

        $found2 = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            'cust_1',
            'prof_2'
        );
        self::assertNotNull($found2);
        self::assertSame($profile2->getId(), $found2->getId());

        $found3 = $this->repository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            'cust_2',
            'prof_1'
        );
        self::assertNotNull($found3);
        self::assertSame($profile3->getId(), $found3->getId());
    }
}

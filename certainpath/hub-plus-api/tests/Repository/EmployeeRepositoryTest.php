<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\User;
use App\Repository\EmployeeRepository;
use App\Tests\AbstractKernelTestCase;
use Ramsey\Uuid\Uuid;

class EmployeeRepositoryTest extends AbstractKernelTestCase
{
    private EmployeeRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EmployeeRepository $employeeRepository */
        $employeeRepository = $this->entityManager->getRepository(Employee::class);
        $this->repository = $employeeRepository;
    }

    public function testSave(): void
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);

        $user = new User();
        $user->setEmail($this->faker->email());
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $this->entityManager->persist($user);

        $employee = new Employee();
        $employee->setUser($user);
        $employee->setCompany($company);
        $employee->setUuid($this->faker->uuid());
        $employee->setFirstName($user->getFirstName());
        $employee->setLastName($user->getLastName());

        self::assertNull($employee->getId());
        $this->repository->save($employee);
        $this->entityManager->flush();
        self::assertNotNull($employee->getId());

        $result = $this->entityManager->find(Employee::class, $employee->getId());
        self::assertSame($employee->getId(), $result->getId());
    }

    public function testFindOneMatchingEmailAndCompany(): void
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);

        $otherCompany = new Company();
        $otherCompany->setCompanyName($this->faker->company());
        $otherCompany->setUuid($this->faker->uuid());
        $this->entityManager->persist($otherCompany);

        $email = 'test.employee@example.com';
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $this->entityManager->persist($user);

        $employee = new Employee();
        $employee->setUser($user);
        $employee->setCompany($company);
        $employee->setUuid($this->faker->uuid());
        $employee->setFirstName($user->getFirstName());
        $employee->setLastName($user->getLastName());
        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        $found = $this->repository->findOneMatchingEmailAndCompany($email, $company);
        self::assertNotNull($found);
        self::assertSame($employee->getId(), $found->getId());

        $notFound = $this->repository->findOneMatchingEmailAndCompany($email, $otherCompany);
        self::assertNull($notFound);

        $notFound2 = $this->repository->findOneMatchingEmailAndCompany('nonexistent@example.com', $company);
        self::assertNull($notFound2);
    }

    public function testFindEmployeeByUuid(): void
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);

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

        $uuid = $employee->getUuid();
        self::assertNotNull($uuid, 'UUID should be generated');

        $found = $this->repository->findEmployeeByUuid($uuid);
        self::assertNotNull($found);
        self::assertSame($employee->getId(), $found->getId());

        $notFound = $this->repository->findEmployeeByUuid(Uuid::uuid4()->toString());
        self::assertNull($notFound);
    }
}

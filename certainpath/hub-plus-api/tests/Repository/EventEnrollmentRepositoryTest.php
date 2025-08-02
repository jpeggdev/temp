<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventEnrollment;
use App\Entity\EventSession;
use App\Entity\User;
use App\Enum\EventCheckoutSessionStatus;
use App\Repository\EventEnrollmentRepository;
use App\Tests\AbstractKernelTestCase;

class EventEnrollmentRepositoryTest extends AbstractKernelTestCase
{
    private EventEnrollmentRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EventEnrollmentRepository $enrollmentRepository */
        $enrollmentRepository = $this->entityManager->getRepository(EventEnrollment::class);
        $this->repository = $enrollmentRepository;
    }

    private function createTestEntities(): array
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

        $event = new Event();
        $event->setEventCode($this->faker->bothify('EV-####'));
        $event->setEventName($this->faker->sentence());
        $event->setEventDescription($this->faker->paragraph());
        $event->setEventPrice(100.00);
        $event->setUuid($this->faker->uuid());
        $this->entityManager->persist($event);

        $eventSession = new EventSession();
        $eventSession->setEvent($event);
        $eventSession->setName($this->faker->sentence(3));
        $eventSession->setStartDate(new \DateTimeImmutable('+1 day'));
        $eventSession->setEndDate(new \DateTimeImmutable('+2 days'));
        $eventSession->setMaxEnrollments(10);
        $eventSession->setUuid($this->faker->uuid());
        $this->entityManager->persist($eventSession);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setContactName($this->faker->name());
        $eventCheckout->setContactEmail($this->faker->email());
        $eventCheckout->setCreatedBy($employee);
        $eventCheckout->setEventSession($eventSession);
        $eventCheckout->setCompany($company);
        $eventCheckout->setUuid($this->faker->uuid());
        $eventCheckout->setStatus(EventCheckoutSessionStatus::IN_PROGRESS);
        $this->entityManager->persist($eventCheckout);

        $enrollmentWithEmployee = new EventEnrollment();
        $enrollmentWithEmployee->setEventSession($eventSession);
        $enrollmentWithEmployee->setEmployee($employee);
        $enrollmentWithEmployee->setEmail($user->getEmail());
        $enrollmentWithEmployee->setFirstName($user->getFirstName());
        $enrollmentWithEmployee->setLastName($user->getLastName());
        $enrollmentWithEmployee->setEnrolledAt(new \DateTimeImmutable());
        $enrollmentWithEmployee->setEventCheckout($eventCheckout);
        $this->entityManager->persist($enrollmentWithEmployee);

        $emailOnlyEnrollment = new EventEnrollment();
        $emailOnlyEnrollment->setEventSession($eventSession);
        $emailOnlyEnrollment->setEmail('email.only@example.com');
        $emailOnlyEnrollment->setFirstName($this->faker->firstName());
        $emailOnlyEnrollment->setLastName($this->faker->lastName());
        $emailOnlyEnrollment->setEnrolledAt(new \DateTimeImmutable());
        $emailOnlyEnrollment->setEventCheckout($eventCheckout); // Set the required checkout
        $this->entityManager->persist($emailOnlyEnrollment);

        $this->entityManager->flush();

        return [
            'company' => $company,
            'user' => $user,
            'employee' => $employee,
            'event' => $event,
            'eventSession' => $eventSession,
            'eventCheckout' => $eventCheckout,
            'enrollmentWithEmployee' => $enrollmentWithEmployee,
            'emailOnlyEnrollment' => $emailOnlyEnrollment,
        ];
    }

    public function testCountEnrollmentsForSession(): void
    {
        $entities = $this->createTestEntities();

        $count = $this->repository->countEnrollmentsForSession($entities['eventSession']);
        self::assertSame(2, $count);

        $newSession = new EventSession();
        $newSession->setEvent($entities['event']);
        $newSession->setName($this->faker->sentence(3));
        $newSession->setStartDate(new \DateTimeImmutable('+3 days'));
        $newSession->setEndDate(new \DateTimeImmutable('+4 days'));
        $newSession->setMaxEnrollments(10);
        $newSession->setUuid($this->faker->uuid());
        $this->entityManager->persist($newSession);
        $this->entityManager->flush();

        $count = $this->repository->countEnrollmentsForSession($newSession);
        self::assertSame(0, $count);
    }

    public function testFindOneByEventSessionAndEmployee(): void
    {
        $entities = $this->createTestEntities();

        $found = $this->repository->findOneByEventSessionAndEmployee(
            $entities['eventSession']->getId(),
            $entities['employee']->getId()
        );

        self::assertNotNull($found);
        self::assertSame($entities['enrollmentWithEmployee']->getId(), $found->getId());

        $notFound = $this->repository->findOneByEventSessionAndEmployee(
            $entities['eventSession']->getId(),
            999999
        );

        self::assertNull($notFound);

        $notFound2 = $this->repository->findOneByEventSessionAndEmployee(
            999999,
            $entities['employee']->getId()
        );

        self::assertNull($notFound2);
    }

    public function testFindOneByEventSessionAndEmail(): void
    {
        $entities = $this->createTestEntities();

        $found = $this->repository->findOneByEventSessionAndEmail(
            $entities['eventSession']->getId(),
            $entities['user']->getEmail()
        );

        self::assertNotNull($found);
        self::assertSame($entities['enrollmentWithEmployee']->getId(), $found->getId());

        $found2 = $this->repository->findOneByEventSessionAndEmail(
            $entities['eventSession']->getId(),
            'email.only@example.com'
        );

        self::assertNotNull($found2);
        self::assertSame($entities['emailOnlyEnrollment']->getId(), $found2->getId());

        $notFound = $this->repository->findOneByEventSessionAndEmail(
            $entities['eventSession']->getId(),
            'nonexistent@example.com'
        );

        self::assertNull($notFound);

        $notFound2 = $this->repository->findOneByEventSessionAndEmail(
            999999,
            $entities['user']->getEmail()
        );

        self::assertNull($notFound2);
    }

    public function testFindAllByEventSessionId(): void
    {
        $entities = $this->createTestEntities();

        $enrollments = $this->repository->findAllByEventSessionId($entities['eventSession']->getId());

        self::assertCount(2, $enrollments);
        self::assertTrue($enrollments->contains($entities['enrollmentWithEmployee']));
        self::assertTrue($enrollments->contains($entities['emailOnlyEnrollment']));

        $emptyCollection = $this->repository->findAllByEventSessionId(999999);
        self::assertCount(0, $emptyCollection);
    }
}

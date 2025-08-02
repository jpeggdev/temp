<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Entity\User;
use App\Repository\EventEnrollmentWaitlistRepository;
use App\Tests\AbstractKernelTestCase;

class EventEnrollmentWaitlistRepositoryTest extends AbstractKernelTestCase
{
    private EventEnrollmentWaitlistRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EventEnrollmentWaitlistRepository $waitlistRepository */
        $waitlistRepository = $this->entityManager->getRepository(EventEnrollmentWaitlist::class);
        $this->repository = $waitlistRepository;
    }

    private function createBaseEntities(): array
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);

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

        $otherEventSession = new EventSession();
        $otherEventSession->setEvent($event);
        $otherEventSession->setName($this->faker->sentence(3));
        $otherEventSession->setStartDate(new \DateTimeImmutable('+3 days'));
        $otherEventSession->setEndDate(new \DateTimeImmutable('+4 days'));
        $otherEventSession->setMaxEnrollments(10);
        $otherEventSession->setUuid($this->faker->uuid());
        $this->entityManager->persist($otherEventSession);

        $this->entityManager->flush();

        return [
            'company' => $company,
            'event' => $event,
            'eventSession' => $eventSession,
            'otherEventSession' => $otherEventSession,
        ];
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

    private function createWaitlistEntry(
        EventSession $session,
        ?Employee $employee = null,
        ?string $email = null,
        ?int $position = null,
    ): EventEnrollmentWaitlist {
        $waitlist = new EventEnrollmentWaitlist();
        $waitlist->setEventSession($session);
        $waitlist->setWaitlistedAt(new \DateTimeImmutable());

        if ($employee) {
            $waitlist->setEmployee($employee);
            $waitlist->setEmail($employee->getUser()->getEmail());
            $waitlist->setFirstName($employee->getFirstName());
            $waitlist->setLastName($employee->getLastName());
        } else {
            $waitlist->setEmail($email ?? $this->faker->email());
            $waitlist->setFirstName($this->faker->firstName());
            $waitlist->setLastName($this->faker->lastName());
        }

        if (null !== $position) {
            $waitlist->setWaitlistPosition($position);
        }

        $this->entityManager->persist($waitlist);
        $this->entityManager->flush();

        return $waitlist;
    }

    public function testGetMaxWaitlistPosition(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];
        $otherSession = $entities['otherEventSession'];

        $initialMax = $this->repository->getMaxWaitlistPosition($session);
        self::assertNull($initialMax);

        $this->createWaitlistEntry($session, null, null, 1);
        $this->createWaitlistEntry($session, null, null, 3);
        $this->createWaitlistEntry($session, null, null, 5);

        $this->createWaitlistEntry($otherSession, null, null, 10);

        $maxPosition = $this->repository->getMaxWaitlistPosition($session);
        self::assertSame(5, $maxPosition);

        $this->createWaitlistEntry($session, null, null, 8);
        $newMaxPosition = $this->repository->getMaxWaitlistPosition($session);
        self::assertSame(8, $newMaxPosition);

        $otherMaxPosition = $this->repository->getMaxWaitlistPosition($otherSession);
        self::assertSame(10, $otherMaxPosition);
    }

    public function testFindOneByEventSessionAndEmployee(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];
        $company = $entities['company'];

        $employee1 = $this->createEmployee($company);
        $employee2 = $this->createEmployee($company);

        $waitlist1 = $this->createWaitlistEntry($session, $employee1, null, 1);
        $waitlist2 = $this->createWaitlistEntry($session, $employee2, null, 2);

        $found1 = $this->repository->findOneByEventSessionAndEmployee(
            $session->getId(),
            $employee1->getId()
        );

        self::assertNotNull($found1);
        self::assertSame($waitlist1->getId(), $found1->getId());

        $found2 = $this->repository->findOneByEventSessionAndEmployee(
            $session->getId(),
            $employee2->getId()
        );

        self::assertNotNull($found2);
        self::assertSame($waitlist2->getId(), $found2->getId());

        $notFound = $this->repository->findOneByEventSessionAndEmployee(
            $session->getId(),
            99999
        );

        self::assertNull($notFound);

        $notFound2 = $this->repository->findOneByEventSessionAndEmployee(
            99999,
            $employee1->getId()
        );

        self::assertNull($notFound2);
    }

    public function testFindOneByEventSessionAndEmail(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];

        $email1 = 'test1@example.com';
        $email2 = 'test2@example.com';

        $waitlist1 = $this->createWaitlistEntry($session, null, $email1, 1);
        $waitlist2 = $this->createWaitlistEntry($session, null, $email2, 2);

        $found1 = $this->repository->findOneByEventSessionAndEmail(
            $session->getId(),
            $email1
        );

        self::assertNotNull($found1);
        self::assertSame($waitlist1->getId(), $found1->getId());

        $found2 = $this->repository->findOneByEventSessionAndEmail(
            $session->getId(),
            $email2
        );

        self::assertNotNull($found2);
        self::assertSame($waitlist2->getId(), $found2->getId());

        $notFound = $this->repository->findOneByEventSessionAndEmail(
            $session->getId(),
            'nonexistent@example.com'
        );

        self::assertNull($notFound);

        $notFound2 = $this->repository->findOneByEventSessionAndEmail(
            99999,
            $email1
        );

        self::assertNull($notFound2);
    }

    public function testFindWithEmployee(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];
        $company = $entities['company'];

        $employee = $this->createEmployee($company);
        $waitlist = $this->createWaitlistEntry($session, $employee, null, 1);

        $foundByEmployee = $this->repository->findOneByEventSessionAndEmployee(
            $session->getId(),
            $employee->getId()
        );

        self::assertNotNull($foundByEmployee);
        self::assertSame($waitlist->getId(), $foundByEmployee->getId());

        $foundByEmail = $this->repository->findOneByEventSessionAndEmail(
            $session->getId(),
            $employee->getUser()->getEmail()
        );

        self::assertNotNull($foundByEmail);
        self::assertSame($waitlist->getId(), $foundByEmail->getId());
    }
}

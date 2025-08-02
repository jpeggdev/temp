<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventSession;
use App\Entity\User;
use App\Enum\EventCheckoutSessionStatus;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EventCheckoutNotFoundException;
use App\Repository\EventCheckoutRepository;
use App\Tests\AbstractKernelTestCase;
use Ramsey\Uuid\Uuid;

class EventCheckoutRepositoryTest extends AbstractKernelTestCase
{
    private EventCheckoutRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EventCheckoutRepository $eventCheckoutRepository */
        $eventCheckoutRepository = $this->entityManager->getRepository(EventCheckout::class);
        $this->repository = $eventCheckoutRepository;
    }

    private function createBaseEntities(): array
    {
        $company = new Company();
        $company->setCompanyName($this->faker->company());
        $company->setUuid($this->faker->uuid());
        $this->entityManager->persist($company);

        $otherCompany = new Company();
        $otherCompany->setCompanyName($this->faker->company());
        $otherCompany->setUuid($this->faker->uuid());
        $this->entityManager->persist($otherCompany);

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
            'otherCompany' => $otherCompany,
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

    private function createEventCheckout(
        EventSession $session,
        Employee $employee,
        Company $company,
        ?EventCheckoutSessionStatus $status = null,
        ?\DateTimeImmutable $expiresAt = null,
        bool $finalized = false,
        ?string $confirmationNumber = null,
    ): EventCheckout {
        $checkout = new EventCheckout();
        $checkout->setContactName($this->faker->name());
        $checkout->setContactEmail($this->faker->email());
        $checkout->setCreatedBy($employee);
        $checkout->setEventSession($session);
        $checkout->setCompany($company);
        $checkout->setStatus($status ?? EventCheckoutSessionStatus::IN_PROGRESS);

        if ($confirmationNumber) {
            $checkout->setConfirmationNumber($confirmationNumber);
        }

        if ($finalized) {
            $checkout->setFinalizedAt(new \DateTimeImmutable());
        }

        if ($expiresAt) {
            $checkout->setReservationExpiresAt($expiresAt);
        } else {
            $checkout->setReservationExpiresAt(
                new \DateTimeImmutable(
                    '+1 hour',
                    new \DateTimeZone('UTC')
                )
            );
        }

        $this->repository->save($checkout, true);

        return $checkout;
    }

    public function testSave(): void
    {
        $entities = $this->createBaseEntities();
        $employee = $this->createEmployee($entities['company']);

        $checkout = new EventCheckout();
        $checkout->setContactName($this->faker->name());
        $checkout->setContactEmail($this->faker->email());
        $checkout->setCreatedBy($employee);
        $checkout->setEventSession($entities['eventSession']);
        $checkout->setCompany($entities['company']);
        $checkout->setStatus(EventCheckoutSessionStatus::IN_PROGRESS);
        $checkout->setReservationExpiresAt(
            new \DateTimeImmutable(
                '+1 hour',
                new \DateTimeZone('UTC')
            )
        );

        self::assertNull($checkout->getId());

        $this->repository->save($checkout, true);

        self::assertNotNull($checkout->getId());

        $found = $this->repository->find($checkout->getId());
        self::assertNotNull($found);
        self::assertNotNull($found->getUuid());
    }

    public function testFindInProgressSession(): void
    {
        $entities = $this->createBaseEntities();
        $company = $entities['company'];
        $otherCompany = $entities['otherCompany'];
        $session = $entities['eventSession'];
        $otherSession = $entities['otherEventSession'];

        $employee1 = $this->createEmployee($company);
        $employee2 = $this->createEmployee($company);

        $checkout1 = $this->createEventCheckout(
            $session,
            $employee1,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $checkout2 = $this->createEventCheckout(
            $session,
            $employee1,
            $company,
            EventCheckoutSessionStatus::COMPLETED,
            null,
            true
        );

        $checkout3 = $this->createEventCheckout(
            $session,
            $employee2,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $checkout4 = $this->createEventCheckout(
            $otherSession,
            $employee1,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $checkout5 = $this->createEventCheckout(
            $session,
            $employee1,
            $otherCompany,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $found1 = $this->repository->findInProgressSession($employee1, $session, $company);
        self::assertNotNull($found1);
        self::assertSame($checkout1->getId(), $found1->getId());

        $found2 = $this->repository->findInProgressSession($employee2, $session, $company);
        self::assertNotNull($found2);
        self::assertSame($checkout3->getId(), $found2->getId());

        $found3 = $this->repository->findInProgressSession($employee1, $otherSession, $company);
        self::assertNotNull($found3);
        self::assertSame($checkout4->getId(), $found3->getId());

        $found4 = $this->repository->findInProgressSession($employee1, $session, $otherCompany);
        self::assertNotNull($found4);
        self::assertSame($checkout5->getId(), $found4->getId());
    }

    public function testFindEarliestActiveCheckoutForUserAndSession(): void
    {
        $entities = $this->createBaseEntities();
        $company = $entities['company'];
        $session = $entities['eventSession'];

        $employee1 = $this->createEmployee($company);
        $employee2 = $this->createEmployee($company);
        $employee3 = $this->createEmployee($company);
        $employee4 = $this->createEmployee($company);
        $employee5 = $this->createEmployee($company);
        $employee6 = $this->createEmployee($company);

        $this->createEventCheckout(
            $session,
            $employee1,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS,
            new \DateTimeImmutable('+2 hours')
        );

        $checkout2 = $this->createEventCheckout(
            $session,
            $employee2,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS,
            new \DateTimeImmutable('+1 hour')
        );

        $this->createEventCheckout(
            $session,
            $employee3,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS,
            new \DateTimeImmutable('+3 hours')
        );

        $this->createEventCheckout(
            $session,
            $employee4,
            $company,
            EventCheckoutSessionStatus::COMPLETED,
            new \DateTimeImmutable('+30 minutes'),
            true
        );

        $this->createEventCheckout(
            $session,
            $employee5,
            $company,
            EventCheckoutSessionStatus::EXPIRED,
            new \DateTimeImmutable('-30 minutes')
        );

        $this->createEventCheckout(
            $session,
            $employee6,
            $company,
            EventCheckoutSessionStatus::CANCELED,
            new \DateTimeImmutable('+15 minutes')
        );

        $found = $this->repository->findEarliestActiveCheckoutForUserAndSession($session, $employee2, $company);
        self::assertNotNull($found);
        self::assertSame($checkout2->getId(), $found->getId());
    }

    public function testCancelActiveSessionsForEmployeeAndSession(): void
    {
        $entities = $this->createBaseEntities();
        $company = $entities['company'];
        $session = $entities['eventSession'];
        $otherSession = $entities['otherEventSession'];

        $employee = $this->createEmployee($company);
        $otherEmployee = $this->createEmployee($company);

        $checkout1 = $this->createEventCheckout(
            $session,
            $employee,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $checkout3 = $this->createEventCheckout(
            $session,
            $otherEmployee,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $checkout4 = $this->createEventCheckout(
            $otherSession,
            $employee,
            $company,
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $checkout5 = $this->createEventCheckout(
            $session,
            $employee,
            $company,
            EventCheckoutSessionStatus::COMPLETED,
            null,
            true
        );

        $this->repository->cancelActiveSessionsForEmployeeAndSession($employee, $company, $session);

        $this->entityManager->clear();
        $checkout1 = $this->repository->find($checkout1->getId());
        $checkout3 = $this->repository->find($checkout3->getId());
        $checkout4 = $this->repository->find($checkout4->getId());
        $checkout5 = $this->repository->find($checkout5->getId());

        self::assertSame(EventCheckoutSessionStatus::CANCELED, $checkout1->getStatus());
        self::assertSame(EventCheckoutSessionStatus::IN_PROGRESS, $checkout3->getStatus());
        self::assertSame(EventCheckoutSessionStatus::IN_PROGRESS, $checkout4->getStatus());
        self::assertSame(EventCheckoutSessionStatus::COMPLETED, $checkout5->getStatus());
    }

    public function testFindOneByUuid(): void
    {
        $entities = $this->createBaseEntities();
        $employee = $this->createEmployee($entities['company']);

        $checkout = $this->createEventCheckout(
            $entities['eventSession'],
            $employee,
            $entities['company'],
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $uuid = $checkout->getUuid();
        self::assertNotNull($uuid);

        $found = $this->repository->findOneByUuid($uuid);
        self::assertNotNull($found);
        self::assertSame($checkout->getId(), $found->getId());

        $nonExistentUuid = Uuid::uuid4()->toString();
        $notFound = $this->repository->findOneByUuid($nonExistentUuid);
        self::assertNull($notFound);
    }

    public function testFindOneByConfirmationNumber(): void
    {
        $entities = $this->createBaseEntities();
        $employee = $this->createEmployee($entities['company']);

        $confirmationNumber = 'CONF-12345';
        $checkout = $this->createEventCheckout(
            $entities['eventSession'],
            $employee,
            $entities['company'],
            EventCheckoutSessionStatus::COMPLETED,
            null,
            true,
            $confirmationNumber
        );

        $found = $this->repository->findOneByConfirmationNumber($confirmationNumber);
        self::assertNotNull($found);
        self::assertSame($checkout->getId(), $found->getId());

        $notFound = $this->repository->findOneByConfirmationNumber('non-existent-number');
        self::assertNull($notFound);
    }

    public function testFindOneByUuidOrFail(): void
    {
        $entities = $this->createBaseEntities();
        $employee = $this->createEmployee($entities['company']);

        $checkout = $this->createEventCheckout(
            $entities['eventSession'],
            $employee,
            $entities['company'],
            EventCheckoutSessionStatus::IN_PROGRESS
        );

        $uuid = $checkout->getUuid();
        self::assertNotNull($uuid);

        $found = $this->repository->findOneByUuidOrFail($uuid);
        self::assertNotNull($found);
        self::assertSame($checkout->getId(), $found->getId());

        $this->expectException(EventCheckoutNotFoundException::class);
        $nonExistentUuid = Uuid::uuid4()->toString();
        $this->repository->findOneByUuidOrFail($nonExistentUuid);
    }
}

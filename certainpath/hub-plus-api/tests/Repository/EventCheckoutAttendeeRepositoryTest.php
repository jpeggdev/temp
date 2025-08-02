<?php

namespace App\Tests\Repository;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventSession;
use App\Entity\User;
use App\Enum\EventCheckoutSessionStatus;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Tests\AbstractKernelTestCase;

class EventCheckoutAttendeeRepositoryTest extends AbstractKernelTestCase
{
    private EventCheckoutAttendeeRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        /** @var EventCheckoutAttendeeRepository $attendeeRepository */
        $attendeeRepository = $this->entityManager->getRepository(EventCheckoutAttendee::class);
        $this->repository = $attendeeRepository;
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail($this->faker->email());
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $this->entityManager->persist($user);

        return $user;
    }

    private function createEmployee(User $user, Company $company): Employee
    {
        $employee = new Employee();
        $employee->setUser($user);
        $employee->setCompany($company);
        $employee->setFirstName($user->getFirstName());
        $employee->setLastName($user->getLastName());
        $this->entityManager->persist($employee);

        return $employee;
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

        $user = $this->createUser();

        $employee = $this->createEmployee($user, $company);

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
            'user' => $user,
            'employee' => $employee,
            'event' => $event,
            'eventSession' => $eventSession,
            'otherEventSession' => $otherEventSession,
        ];
    }

    private function createCheckout(
        EventSession $session,
        Employee $employee,
        Company $company,
        bool $isFinalized = false,
        ?\DateTimeImmutable $expiresAt = null,
        ?EventCheckoutSessionStatus $status = null,
    ): EventCheckout {
        $checkout = new EventCheckout();
        $checkout->setContactName($this->faker->name());
        $checkout->setContactEmail($this->faker->email());
        $checkout->setCreatedBy($employee);
        $checkout->setEventSession($session);
        $checkout->setCompany($company);
        $checkout->setUuid($this->faker->uuid());
        $checkout->setStatus($status ?? EventCheckoutSessionStatus::IN_PROGRESS);

        if ($isFinalized) {
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

        $this->entityManager->persist($checkout);
        $this->entityManager->flush();

        return $checkout;
    }

    private function createAttendee(
        EventCheckout $checkout,
        bool $isSelected,
        bool $isWaitlist,
    ): EventCheckoutAttendee {
        $attendee = new EventCheckoutAttendee();
        $attendee->setEventCheckout($checkout);
        $attendee->setFirstName($this->faker->firstName());
        $attendee->setLastName($this->faker->lastName());
        $attendee->setEmail($this->faker->email());
        $attendee->setIsSelected($isSelected);
        $attendee->setIsWaitlist($isWaitlist);

        $this->entityManager->persist($attendee);
        $this->entityManager->flush();

        return $attendee;
    }

    public function testCountActiveAttendeesForSession(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];
        $mainEmployee = $entities['employee'];
        $company = $entities['company'];

        $activeCheckout = $this->createCheckout($session, $mainEmployee, $company);

        $this->createAttendee($activeCheckout, true, false);
        $this->createAttendee($activeCheckout, true, false);
        $this->createAttendee($activeCheckout, true, false);

        $this->createAttendee($activeCheckout, true, true);

        $this->createAttendee($activeCheckout, false, false);

        $user2 = $this->createUser();
        $employee2 = $this->createEmployee($user2, $company);

        $finalizedCheckout = $this->createCheckout(
            $session,
            $employee2,
            $company,
            true,
            null,
            EventCheckoutSessionStatus::COMPLETED
        );
        $this->createAttendee($finalizedCheckout, true, false);

        $user3 = $this->createUser();
        $employee3 = $this->createEmployee($user3, $company);

        $expiredCheckout = $this->createCheckout(
            $session,
            $employee3,
            $company,
            false,
            new \DateTimeImmutable('-1 hour', new \DateTimeZone('UTC')),
            EventCheckoutSessionStatus::EXPIRED
        );
        $this->createAttendee($expiredCheckout, true, false);

        $count = $this->repository->countActiveAttendeesForSession($session);
        self::assertSame(3, $count);
    }

    public function testCountActiveAttendeesForSessionByEmployee(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];
        $mainEmployee = $entities['employee'];
        $company = $entities['company'];

        $mainCheckout = $this->createCheckout($session, $mainEmployee, $company);

        $this->createAttendee($mainCheckout, true, false);
        $this->createAttendee($mainCheckout, true, false);

        $otherUser = $this->createUser();
        $otherEmployee = $this->createEmployee($otherUser, $company);

        $otherCheckout = $this->createCheckout($entities['otherEventSession'], $otherEmployee, $company);

        $this->createAttendee($otherCheckout, true, false);
        $this->createAttendee($otherCheckout, true, false);
        $this->createAttendee($otherCheckout, true, false);

        $mainCount = $this->repository->countActiveAttendeesForSessionByEmployee(
            $session,
            $mainEmployee,
            $company
        );
        self::assertSame(2, $mainCount);

        $otherCount = $this->repository->countActiveAttendeesForSessionByEmployee(
            $session,
            $otherEmployee,
            $company
        );
        self::assertSame(0, $otherCount);

        $otherSessionCount = $this->repository->countActiveAttendeesForSessionByEmployee(
            $entities['otherEventSession'],
            $otherEmployee,
            $company
        );
        self::assertSame(3, $otherSessionCount);
    }

    public function testCountInProgressAttendees(): void
    {
        $entities = $this->createBaseEntities();
        $session = $entities['eventSession'];
        $employee = $entities['employee'];
        $company = $entities['company'];
        $otherCompany = $entities['otherCompany'];

        $excludeCheckout = $this->createCheckout($session, $employee, $company);

        $this->createAttendee($excludeCheckout, true, false);
        $this->createAttendee($excludeCheckout, true, false);

        $user2 = $this->createUser();
        $employee2 = $this->createEmployee($user2, $otherCompany);

        $otherCheckout = $this->createCheckout($session, $employee2, $otherCompany);

        $this->createAttendee($otherCheckout, true, false);
        $this->createAttendee($otherCheckout, true, false);
        $this->createAttendee($otherCheckout, true, false);

        $this->createAttendee($otherCheckout, true, true);

        $this->createAttendee($otherCheckout, false, false);

        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $count = $this->repository->countInProgressAttendees(
            $session->getId(),
            $excludeCheckout->getId(),
            $now
        );
        self::assertSame(3, $count);
    }
}

<?php

/** @noinspection ALL */

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service;

use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\Service\ApplySeatingAndReservationService;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventEnrollmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ApplySeatingAndReservationServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private ApplySeatingAndReservationService $service;
    private EventCheckout|MockInterface|LegacyMockInterface $attendeeRepositoryMock;
    private EventCheckout|MockInterface|LegacyMockInterface $enrollmentRepositoryMock;

    protected function setUp(): void
    {
        $this->attendeeRepositoryMock = \Mockery::mock(EventCheckoutAttendeeRepository::class);
        $this->enrollmentRepositoryMock = \Mockery::mock(EventEnrollmentRepository::class);

        $this->service = new ApplySeatingAndReservationService(
            $this->attendeeRepositoryMock,
            $this->enrollmentRepositoryMock
        );
    }

    // region createEventSessionMock
    private function createEventSessionMock(
        int $maxEnrollments = 10,
        int $id = 1,
    ): EventSession|MockInterface|LegacyMockInterface {
        $session = \Mockery::mock(EventSession::class);
        $session->shouldReceive('getId')->andReturn($id);
        $session->shouldReceive('getMaxEnrollments')->andReturn($maxEnrollments);

        return $session;
    }
    // endregion

    // region createEventCheckoutMock
    private function createEventCheckoutMock(
        EventSession|MockInterface|LegacyMockInterface $session,
        int $id = 1,
        ?\DateTimeImmutable $reservationExpiresAt = null,
    ): EventCheckout|MockInterface|LegacyMockInterface {
        $checkout = \Mockery::mock(EventCheckout::class);
        $checkout->shouldReceive('getId')->andReturn($id);
        $checkout->shouldReceive('getEventSession')->andReturn($session);

        $attendees = new ArrayCollection();
        $checkout->shouldReceive('getEventCheckoutAttendees')->andReturn($attendees);

        $checkout->shouldReceive('addEventCheckoutAttendee')->andReturnUsing(
            function ($attendee) use ($attendees) {
                $attendees->add($attendee);

                return $this;
            }
        );

        $checkout->shouldReceive('getReservationExpiresAt')->andReturn($reservationExpiresAt);
        $checkout->shouldReceive('setReservationExpiresAt');

        return $checkout;
    }
    // endregion

    // region createAttendee
    private function createAttendee(
        EventCheckout|MockInterface|LegacyMockInterface $checkout,
        string $name,
        bool $isWaitlist = false,
    ): EventCheckoutAttendee {
        $attendee = new EventCheckoutAttendee();
        $attendee->setFirstName($name);
        $attendee->setLastName('Test');
        $attendee->setEmail("$name@example.com");
        $attendee->setIsWaitlist($isWaitlist);
        $attendee->setIsSelected(true);
        $checkout->getEventCheckoutAttendees()->add($attendee);

        return $attendee;
    }
    // endregion

    // region testApplyWithNoEventSession
    public function testApplyWithNoEventSession(): void
    {
        /** @var EventCheckout|MockInterface|LegacyMockInterface $checkout */
        $checkout = \Mockery::mock(EventCheckout::class);
        $checkout->shouldReceive('getEventSession')->andReturnNull();
        $this->service->apply($checkout);
        $this->assertTrue(true);
    }
    // endregion

    // region testApplyWithNoAttendees

    public function testApplyWithNoAttendees(): void
    {
        $session = $this->createEventSessionMock(10);
        $checkout = $this->createEventCheckoutMock(
            $session,
            1,
            new \DateTimeImmutable('+15 minutes')
        );

        $this->service->apply($checkout);

        $checkout->shouldHaveReceived('setReservationExpiresAt')->with(null)->once();
    }
    // endregion

    // region testApplyWithEnoughCapacityForAllAttendees
    public function testApplyWithEnoughCapacityForAllAttendees(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $checkout = $this->createEventCheckoutMock($session, 1);

        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');
        $attendee3 = $this->createAttendee($checkout, 'Bob');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(2);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(3);

        $this->service->apply($checkout);

        $this->assertFalse($attendee1->isWaitlist());
        $this->assertFalse($attendee2->isWaitlist());
        $this->assertFalse($attendee3->isWaitlist());

        $checkout->shouldHaveReceived('setReservationExpiresAt')->once();
    }
    // endregion

    // region testApplyWithSomeAttendeesSeatsSomeWaitlisted
    public function testApplyWithSomeAttendeesSeatsSomeWaitlisted(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $checkout = $this->createEventCheckoutMock($session, 1);

        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');
        $attendee3 = $this->createAttendee($checkout, 'Bob');
        $attendee4 = $this->createAttendee($checkout, 'Alice');
        $attendee5 = $this->createAttendee($checkout, 'Charlie');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(3);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(5);

        $this->service->apply($checkout);

        $this->assertFalse($attendee1->isWaitlist());
        $this->assertFalse($attendee2->isWaitlist());
        $this->assertTrue($attendee3->isWaitlist());
        $this->assertTrue($attendee4->isWaitlist());
        $this->assertTrue($attendee5->isWaitlist());

        $checkout->shouldHaveReceived('setReservationExpiresAt')->once();
    }
    // endregion

    // region testApplyWithAllAttendeesWaitlisted
    public function testApplyWithAllAttendeesWaitlisted(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $checkout = $this->createEventCheckoutMock($session, 1);

        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');
        $attendee3 = $this->createAttendee($checkout, 'Bob');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(2);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(8);

        $this->service->apply($checkout);

        $this->assertTrue($attendee1->isWaitlist());
        $this->assertTrue($attendee2->isWaitlist());
        $this->assertTrue($attendee3->isWaitlist());

        $checkout->shouldHaveReceived('setReservationExpiresAt')->with(null)->once();
    }
    // endregion

    // region  testApplyRespectsExistingReservationIfNotExpired
    public function testApplyRespectsExistingReservationIfNotExpired(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $existingExpiration = new \DateTimeImmutable('+20 minutes');
        $checkout = $this->createEventCheckoutMock($session, 1, $existingExpiration);

        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(1);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(2);

        $this->service->apply($checkout);

        $this->assertFalse($attendee1->isWaitlist());
        $this->assertFalse($attendee2->isWaitlist());
    }
    // endregion

    // region testApplyUpdatesExpiredReservation
    public function testApplyUpdatesExpiredReservation(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $expiredReservation = new \DateTimeImmutable('-10 minutes');
        $checkout = $this->createEventCheckoutMock($session, 1, $expiredReservation);

        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(1);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(2);

        $this->service->apply($checkout);

        $this->assertFalse($attendee1->isWaitlist());
        $this->assertFalse($attendee2->isWaitlist());

        $checkout->shouldHaveReceived('setReservationExpiresAt')->once();
    }
    // endregion

    // region testApplyWithExactlyFillingCapacity
    public function testApplyWithExactlyFillingCapacity(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $checkout = $this->createEventCheckoutMock($session, 1);
        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');
        $attendee3 = $this->createAttendee($checkout, 'Bob');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(3);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(4);

        $this->service->apply($checkout);

        $this->assertFalse($attendee1->isWaitlist());
        $this->assertFalse($attendee2->isWaitlist());
        $this->assertFalse($attendee3->isWaitlist());

        $checkout->shouldHaveReceived('setReservationExpiresAt')->once();
    }
    // endregion

    // region testApplyWithMoreCapacityThanMax

    public function testApplyWithMoreCapacityThanMax(): void
    {
        $session = $this->createEventSessionMock(10, 1);
        $checkout = $this->createEventCheckoutMock($session, 1);
        $attendee1 = $this->createAttendee($checkout, 'John');
        $attendee2 = $this->createAttendee($checkout, 'Jane');

        $this->attendeeRepositoryMock->shouldReceive('countInProgressAttendees')
            ->withArgs(function ($sessionId, $checkoutId, $now) {
                return 1 === $sessionId && 1 === $checkoutId && $now instanceof \DateTimeImmutable;
            })
            ->once()
            ->andReturn(5);

        $this->enrollmentRepositoryMock->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->andReturn(6);

        $this->service->apply($checkout);

        $this->assertTrue($attendee1->isWaitlist());
        $this->assertTrue($attendee2->isWaitlist());

        $checkout->shouldHaveReceived('setReservationExpiresAt')->with(null)->once();
    }
    // endregion
}

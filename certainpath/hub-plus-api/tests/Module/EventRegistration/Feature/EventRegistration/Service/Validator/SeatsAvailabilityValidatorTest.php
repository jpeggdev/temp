<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EventCheckoutSessionNotFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NotEnoughSeatsAvailableException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\SeatsAvailabilityValidator;
use App\Repository\EventCheckoutAttendeeRepository;
use App\Repository\EventEnrollmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use PHPUnit\Framework\TestCase;

class SeatsAvailabilityValidatorTest extends TestCase
{
    private EventEnrollmentRepository|Mockery\MockInterface $eventEnrollmentRepository;
    private EventCheckoutAttendeeRepository|Mockery\MockInterface $eventCheckoutAttendeeRepository;
    private SeatsAvailabilityValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventEnrollmentRepository = \Mockery::mock(EventEnrollmentRepository::class);
        $this->eventCheckoutAttendeeRepository = \Mockery::mock(EventCheckoutAttendeeRepository::class);
        $this->validator = new SeatsAvailabilityValidator(
            $this->eventEnrollmentRepository,
            $this->eventCheckoutAttendeeRepository
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testValidateThrowsWhenEventSessionIsNull
    public function testValidateThrowsWhenEventSessionIsNull(): void
    {
        $this->expectException(EventCheckoutSessionNotFoundException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        // Mock EventCheckout with null session
        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn(null);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWhenEnoughSeatsAvailable
    public function testValidateSucceedsWhenEnoughSeatsAvailable(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(10);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendees = [];
        for ($i = 0; $i < 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(5);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(1);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWhenNotEnoughSeatsAvailable
    public function testValidateThrowsWhenNotEnoughSeatsAvailable(): void
    {
        $this->expectException(NotEnoughSeatsAvailableException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(10);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendees = [];
        for ($i = 0; $i < 5; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(6);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(2);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithExactlyEnoughSeats
    public function testValidateSucceedsWithExactlyEnoughSeats(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(10);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendees = [];
        for ($i = 0; $i < 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(6);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(1);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWhenWaitlistedAttendeesExist
    public function testValidateSucceedsWhenWaitlistedAttendeesExist(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(10);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendees = [];

        for ($i = 0; $i < 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        for ($i = 0; $i < 2; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(true);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(7);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWhenNonSelectedAttendeesExist
    public function testValidateSucceedsWhenNonSelectedAttendeesExist(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(10);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendees = [];

        for ($i = 0; $i < 2; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        for ($i = 0; $i < 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(false);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(8);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateWithZeroMaxEnrollments
    public function testValidateWithZeroMaxEnrollments(): void
    {
        $this->expectException(NotEnoughSeatsAvailableException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(0);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendee = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee->shouldReceive('isSelected')->andReturn(true);
        $attendee->shouldReceive('isWaitlist')->andReturn(false);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection([$attendee]));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(0);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithNoNonWaitlistedAttendees
    public function testValidateSucceedsWithNoNonWaitlistedAttendees(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getMaxEnrollments')->andReturn(10);
        $eventSession->shouldReceive('getId')->andReturn(123);

        $attendees = [];
        for ($i = 0; $i < 5; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isSelected')->andReturn(true);
            $attendee->shouldReceive('isWaitlist')->andReturn(true);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getId')->andReturn(456);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->eventEnrollmentRepository
            ->shouldReceive('countEnrollmentsForSession')
            ->once()
            ->with($eventSession)
            ->andReturn(10);

        $this->eventCheckoutAttendeeRepository
            ->shouldReceive('countInProgressAttendees')
            ->once()
            ->with(123, 456, \Mockery::type(\DateTimeImmutable::class))
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion
}

<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor\EnrollmentWaitlistPostProcessor;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentWaitlistRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class EnrollmentWaitlistPostProcessorTest extends TestCase
{
    private EmployeeRepository|Mockery\MockInterface $employeeRepository;
    private EventEnrollmentWaitlistRepository|Mockery\MockInterface $eventEnrollmentWaitlistRepository;
    private EntityManagerInterface|Mockery\MockInterface $entityManager;
    private EnrollmentWaitlistPostProcessor $postProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeRepository = \Mockery::mock(EmployeeRepository::class);
        $this->eventEnrollmentWaitlistRepository = \Mockery::mock(EventEnrollmentWaitlistRepository::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->postProcessor = new EnrollmentWaitlistPostProcessor(
            $this->employeeRepository,
            $this->eventEnrollmentWaitlistRepository,
            $this->entityManager
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testPostProcessDoesNothingWhenEventSessionIsNull
    public function testPostProcessDoesNothingWhenEventSessionIsNull(): void
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
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn(null);

        $this->employeeRepository->shouldNotReceive('findOneMatchingEmailAndCompany');
        $this->eventEnrollmentWaitlistRepository->shouldNotReceive('getMaxWaitlistPosition');
        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessCreatesWaitlistEntriesForWaitlistedAttendees
    public function testPostProcessCreatesWaitlistEntriesForWaitlistedAttendees(): void
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
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $attendees = [];

        $attendee1 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee1->shouldReceive('isWaitlist')->andReturn(false);
        $attendee1->shouldNotReceive('getEmail');
        $attendees[] = $attendee1;

        $attendee2 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee2->shouldReceive('isWaitlist')->andReturn(true);
        $attendee2->shouldReceive('getEmail')->andReturn('jane@example.com');
        $attendee2->shouldReceive('getFirstName')->andReturn('Jane');
        $attendee2->shouldReceive('getLastName')->andReturn('Smith');
        $attendee2->shouldReceive('getSpecialRequests')->andReturn('Vegetarian meal');
        $attendees[] = $attendee2;

        $attendee3 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee3->shouldReceive('isWaitlist')->andReturn(true);
        $attendee3->shouldReceive('getEmail')->andReturn(null);
        $attendee3->shouldReceive('getFirstName')->andReturn('Bob');
        $attendee3->shouldReceive('getLastName')->andReturn('Johnson');
        $attendee3->shouldReceive('getSpecialRequests')->andReturn(null);
        $attendees[] = $attendee3;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')->andReturn(new ArrayCollection($attendees));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $matchingEmployee = new Employee();
        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('jane@example.com', $company)
            ->andReturn($matchingEmployee);

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('getMaxWaitlistPosition')
            ->once()
            ->with($eventSession)
            ->andReturn(5);

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
            ->withArgs(function (EventEnrollmentWaitlist $waitlistEntry) use (
                $eventCheckout,
                $eventSession,
                $matchingEmployee
            ) {
                $this->assertSame($eventSession, $waitlistEntry->getEventSession());
                $this->assertInstanceOf(\DateTimeImmutable::class, $waitlistEntry->getWaitlistedAt());
                $this->assertSame($eventCheckout, $waitlistEntry->getOriginalCheckout());
                $this->assertSame('50', $waitlistEntry->getSeatPrice());

                if ('Jane' === $waitlistEntry->getFirstName()) {
                    $this->assertSame($matchingEmployee, $waitlistEntry->getEmployee());
                    $this->assertSame('Smith', $waitlistEntry->getLastName());
                    $this->assertSame('jane@example.com', $waitlistEntry->getEmail());
                    $this->assertSame('Vegetarian meal', $waitlistEntry->getSpecialRequests());
                    $this->assertSame(6, $waitlistEntry->getWaitlistPosition());

                    return true;
                } elseif ('Bob' === $waitlistEntry->getFirstName()) {
                    $this->assertNull($waitlistEntry->getEmployee());
                    $this->assertSame('Johnson', $waitlistEntry->getLastName());
                    $this->assertNull($waitlistEntry->getEmail());
                    $this->assertNull($waitlistEntry->getSpecialRequests());
                    $this->assertSame(7, $waitlistEntry->getWaitlistPosition());

                    return true;
                }

                return false;
            });

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);
    }
    // endregion

    // region testPostProcessHandlesFirstWaitlistEntryForSession
    public function testPostProcessHandlesFirstWaitlistEntryForSession(): void
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
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(75.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $attendee = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee->shouldReceive('isWaitlist')->andReturn(true);
        $attendee->shouldReceive('getEmail')->andReturn('first@example.com');
        $attendee->shouldReceive('getFirstName')->andReturn('First');
        $attendee->shouldReceive('getLastName')->andReturn('Timer');
        $attendee->shouldReceive('getSpecialRequests')->andReturn('First on waitlist');

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection([$attendee]));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('first@example.com', $company)
            ->andReturn(null);

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('getMaxWaitlistPosition')
            ->once()
            ->with($eventSession)
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
            ->withArgs(function (EventEnrollmentWaitlist $waitlistEntry) use ($eventSession) {
                $this->assertSame($eventSession, $waitlistEntry->getEventSession());
                $this->assertSame('First', $waitlistEntry->getFirstName());
                $this->assertSame('Timer', $waitlistEntry->getLastName());
                $this->assertSame('first@example.com', $waitlistEntry->getEmail());
                $this->assertSame('First on waitlist', $waitlistEntry->getSpecialRequests());
                $this->assertSame(1, $waitlistEntry->getWaitlistPosition());
                $this->assertSame('75', $waitlistEntry->getSeatPrice());

                return true;
            });

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);
    }
    // endregion

    // region testPostProcessHandlesEmptyAttendeeList
    public function testPostProcessHandlesEmptyAttendeeList(): void
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
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $eventSession = \Mockery::mock(EventSession::class);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection([]));

        $this->employeeRepository->shouldNotReceive('findOneMatchingEmailAndCompany');

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('getMaxWaitlistPosition')
            ->once()
            ->with($eventSession)
            ->andReturn(0);

        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessHandlesNoWaitlistedAttendees
    public function testPostProcessHandlesNoWaitlistedAttendees(): void
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
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $eventSession = \Mockery::mock(EventSession::class);

        $attendees = [];
        for ($i = 0; $i < 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->employeeRepository->shouldNotReceive('findOneMatchingEmailAndCompany');

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('getMaxWaitlistPosition')
            ->once()
            ->with($eventSession)
            ->andReturn(0);

        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessWithNullChargeResponse
    public function testPostProcessWithNullChargeResponse(): void
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
        $chargeResponse = null;

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(60.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $attendee = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee->shouldReceive('isWaitlist')->andReturn(true);
        $attendee->shouldReceive('getEmail')->andReturn('waitlist@example.com');
        $attendee->shouldReceive('getFirstName')->andReturn('Wait');
        $attendee->shouldReceive('getLastName')->andReturn('Listed');
        $attendee->shouldReceive('getSpecialRequests')->andReturn(null);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection([$attendee]));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('waitlist@example.com', $company)
            ->andReturn(null);

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('getMaxWaitlistPosition')
            ->once()
            ->with($eventSession)
            ->andReturn(10);

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
            ->withArgs(function (EventEnrollmentWaitlist $waitlistEntry) {
                $this->assertSame(11, $waitlistEntry->getWaitlistPosition());
                $this->assertSame('60', $waitlistEntry->getSeatPrice());

                return true;
            });

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessWithConsecutiveWaitlistPositions
    public function testPostProcessWithConsecutiveWaitlistPositions(): void
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
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(45.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $attendees = [];
        for ($i = 1; $i <= 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isWaitlist')->andReturn(true);
            $attendee->shouldReceive('getEmail')->andReturn("wait{$i}@example.com");
            $attendee->shouldReceive('getFirstName')->andReturn("First{$i}");
            $attendee->shouldReceive('getLastName')->andReturn("Last{$i}");
            $attendee->shouldReceive('getSpecialRequests')->andReturn(null);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->times(3)
            ->andReturn(null);

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('getMaxWaitlistPosition')
            ->once()
            ->with($eventSession)
            ->andReturn(3);

        $expectedPositions = [4, 5, 6];
        $persistCount = 0;

        $this->entityManager
            ->shouldReceive('persist')
            ->times(3)
            ->withArgs(function (EventEnrollmentWaitlist $waitlistEntry) use (&$persistCount, $expectedPositions) {
                $this->assertSame($expectedPositions[$persistCount], $waitlistEntry->getWaitlistPosition());
                $this->assertSame('45', $waitlistEntry->getSeatPrice());
                ++$persistCount;

                return true;
            });

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion
}

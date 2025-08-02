<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventEnrollment;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor\EnrollmentPostProcessor;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class EnrollmentPostProcessorTest extends TestCase
{
    private EmployeeRepository|Mockery\MockInterface $employeeRepository;
    private EntityManagerInterface|Mockery\MockInterface $entityManager;
    private EnrollmentPostProcessor $postProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeRepository = \Mockery::mock(EmployeeRepository::class);
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->postProcessor = new EnrollmentPostProcessor(
            $this->employeeRepository,
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
        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessCreatesEnrollmentsForNonWaitlistedAttendees
    public function testPostProcessCreatesEnrollmentsForNonWaitlistedAttendees(): void
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

        $attendee1 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee1->shouldReceive('isWaitlist')->andReturn(false);
        $attendee1->shouldReceive('getEmail')->andReturn('john@example.com');
        $attendee1->shouldReceive('getFirstName')->andReturn('John');
        $attendee1->shouldReceive('getLastName')->andReturn('Doe');
        $attendee1->shouldReceive('getSpecialRequests')->andReturn('None');
        $attendees[] = $attendee1;

        $attendee2 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee2->shouldReceive('isWaitlist')->andReturn(false);
        $attendee2->shouldReceive('getEmail')->andReturn(null);
        $attendee2->shouldReceive('getFirstName')->andReturn('Jane');
        $attendee2->shouldReceive('getLastName')->andReturn('Smith');
        $attendee2->shouldReceive('getSpecialRequests')->andReturn('Vegetarian meal');
        $attendees[] = $attendee2;

        $attendee3 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee3->shouldReceive('isWaitlist')->andReturn(true);
        $attendee3->shouldNotReceive('getEmail');
        $attendees[] = $attendee3;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')->andReturn(new ArrayCollection($attendees));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $matchingEmployee = new Employee();
        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('john@example.com', $company)
            ->andReturn($matchingEmployee);

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
            ->withArgs(function (EventEnrollment $enrollment) use ($eventCheckout, $eventSession, $matchingEmployee) {
                $this->assertSame($eventCheckout, $enrollment->getEventCheckout());
                $this->assertSame($eventSession, $enrollment->getEventSession());
                $this->assertInstanceOf(\DateTimeImmutable::class, $enrollment->getEnrolledAt());

                if ('John' === $enrollment->getFirstName()) {
                    $this->assertSame($matchingEmployee, $enrollment->getEmployee());
                    $this->assertSame('Doe', $enrollment->getLastName());
                    $this->assertSame('john@example.com', $enrollment->getEmail());
                    $this->assertSame('None', $enrollment->getSpecialRequests());

                    return true;
                } elseif ('Jane' === $enrollment->getFirstName()) {
                    $this->assertNull($enrollment->getEmployee());
                    $this->assertSame('Smith', $enrollment->getLastName());
                    $this->assertNull($enrollment->getEmail());
                    $this->assertSame('Vegetarian meal', $enrollment->getSpecialRequests());

                    return true;
                }

                return false;
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
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')->andReturn(new ArrayCollection([]));

        $this->employeeRepository->shouldNotReceive('findOneMatchingEmailAndCompany');
        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessHandlesAllWaitlistedAttendees
    public function testPostProcessHandlesAllWaitlistedAttendees(): void
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
            $attendee->shouldReceive('isWaitlist')->andReturn(true);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')->andReturn(new ArrayCollection($attendees));

        $this->employeeRepository->shouldNotReceive('findOneMatchingEmailAndCompany');
        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessWithNoMatchingEmployees
    public function testPostProcessWithNoMatchingEmployees(): void
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
        for ($i = 0; $i < 2; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('isWaitlist')->andReturn(false);
            $attendee->shouldReceive('getEmail')->andReturn("user{$i}@example.com");
            $attendee->shouldReceive('getFirstName')->andReturn("First{$i}");
            $attendee->shouldReceive('getLastName')->andReturn("Last{$i}");
            $attendee->shouldReceive('getSpecialRequests')->andReturn(null);
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')->andReturn(new ArrayCollection($attendees));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->times(2)
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('persist')
            ->twice()
            ->withArgs(function (EventEnrollment $enrollment) {
                $this->assertNull($enrollment->getEmployee());

                return true;
            });

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);
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

        $eventSession = \Mockery::mock(EventSession::class);

        $attendee = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee->shouldReceive('isWaitlist')->andReturn(false);
        $attendee->shouldReceive('getEmail')->andReturn('test@example.com');
        $attendee->shouldReceive('getFirstName')->andReturn('Test');
        $attendee->shouldReceive('getLastName')->andReturn('User');
        $attendee->shouldReceive('getSpecialRequests')->andReturn(null);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')->andReturn(new ArrayCollection([$attendee]));
        $eventCheckout->shouldReceive('getCompany')->andReturn($company);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('test@example.com', $company)
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('persist')
            ->once();

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, null);

        $this->assertTrue(true);
    }
    // endregion
}

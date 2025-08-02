<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventEnrollment;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\AttendeeAlreadyEnrolledException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EmployeeAlreadyEnrolledException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventSessionFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\AttendeeAlreadyEnrolledValidator;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class AttendeeAlreadyEnrolledValidatorTest extends TestCase
{
    private EmployeeRepository|Mockery\MockInterface $employeeRepository;
    private EventEnrollmentRepository|Mockery\MockInterface $eventEnrollmentRepository;
    private AttendeeAlreadyEnrolledValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeRepository = \Mockery::mock(EmployeeRepository::class);
        $this->eventEnrollmentRepository = \Mockery::mock(EventEnrollmentRepository::class);

        $this->validator = new AttendeeAlreadyEnrolledValidator(
            $this->employeeRepository,
            $this->eventEnrollmentRepository
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testValidateThrowsWhenNoEventSession
    public function testValidateThrowsWhenNoEventSession(): void
    {
        $this->expectException(NoEventSessionFoundException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 10.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );
        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenEmployeeAlreadyEnrolled
    public function testValidateThrowsWhenEmployeeAlreadyEnrolled(): void
    {
        $this->expectException(EmployeeAlreadyEnrolledException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 10.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getId')->andReturn(999);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);
        $eventCheckout->setCompany($company);

        $attendee = new EventCheckoutAttendee();
        $attendee->setEmail('already.enrolled@mycompany.com');
        $eventCheckout->addEventCheckoutAttendee($attendee);

        $matchedEmployee = \Mockery::mock(Employee::class);
        $matchedEmployee->shouldReceive('getId')->andReturn(123);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('already.enrolled@mycompany.com', $company)
            ->andReturn($matchedEmployee);

        $existingEnrollment = new EventEnrollment();
        $this->eventEnrollmentRepository
            ->shouldReceive('findOneByEventSessionAndEmployee')
            ->once()
            ->with(999, 123)
            ->andReturn($existingEnrollment);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenNonEmployeeEmailIsAlreadyEnrolled
    public function testValidateThrowsWhenNonEmployeeEmailIsAlreadyEnrolled(): void
    {
        $this->expectException(AttendeeAlreadyEnrolledException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 10.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getId')->andReturn(999);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);
        $eventCheckout->setCompany($company);

        $attendee = new EventCheckoutAttendee();
        $attendee->setEmail('external.attendee@example.com');
        $eventCheckout->addEventCheckoutAttendee($attendee);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('external.attendee@example.com', $company)
            ->andReturn(null);

        $existingEnrollment = new EventEnrollment();
        $this->eventEnrollmentRepository
            ->shouldReceive('findOneByEventSessionAndEmail')
            ->once()
            ->with(999, 'external.attendee@example.com')
            ->andReturn($existingEnrollment);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWhenNoExistingEnrollment
    public function testValidateSucceedsWhenNoExistingEnrollment(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 10.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getId')->andReturn(999);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);
        $eventCheckout->setCompany($company);

        $employeeAttendee = new EventCheckoutAttendee();
        $employeeAttendee->setEmail('employee@mycompany.com');
        $eventCheckout->addEventCheckoutAttendee($employeeAttendee);

        $externalAttendee = new EventCheckoutAttendee();
        $externalAttendee->setEmail('external@example.com');
        $eventCheckout->addEventCheckoutAttendee($externalAttendee);

        $matchedEmployee = \Mockery::mock(Employee::class);
        $matchedEmployee->shouldReceive('getId')->andReturn(123);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('employee@mycompany.com', $company)
            ->andReturn($matchedEmployee);

        $this->eventEnrollmentRepository
            ->shouldReceive('findOneByEventSessionAndEmployee')
            ->once()
            ->with(999, 123)
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('external@example.com', $company)
            ->andReturn(null);

        $this->eventEnrollmentRepository
            ->shouldReceive('findOneByEventSessionAndEmail')
            ->once()
            ->with(999, 'external@example.com')
            ->andReturn(null);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion
}

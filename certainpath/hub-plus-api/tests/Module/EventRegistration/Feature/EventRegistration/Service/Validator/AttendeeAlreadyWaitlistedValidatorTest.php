<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventEnrollmentWaitlist;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\AttendeeAlreadyWaitlistedException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventSessionFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\AttendeeAlreadyWaitlistedValidator;
use App\Repository\EmployeeRepository;
use App\Repository\EventEnrollmentWaitlistRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class AttendeeAlreadyWaitlistedValidatorTest extends TestCase
{
    private EmployeeRepository|Mockery\MockInterface $employeeRepository;
    private EventEnrollmentWaitlistRepository|Mockery\MockInterface $eventEnrollmentWaitlistRepository;
    private AttendeeAlreadyWaitlistedValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employeeRepository = \Mockery::mock(EmployeeRepository::class);
        $this->eventEnrollmentWaitlistRepository = \Mockery::mock(EventEnrollmentWaitlistRepository::class);

        $this->validator = new AttendeeAlreadyWaitlistedValidator(
            $this->employeeRepository,
            $this->eventEnrollmentWaitlistRepository
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

    // region testValidateThrowsWhenEmployeeAlreadyWaitlisted
    public function testValidateThrowsWhenEmployeeAlreadyWaitlisted(): void
    {
        $this->expectException(AttendeeAlreadyWaitlistedException::class);

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
        $attendee->setEmail('already.waitlisted@mycompany.com');
        $eventCheckout->addEventCheckoutAttendee($attendee);

        $matchedEmployee = \Mockery::mock(Employee::class);
        $matchedEmployee->shouldReceive('getId')->andReturn(123);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('already.waitlisted@mycompany.com', $company)
            ->andReturn($matchedEmployee);

        $existingWaitlist = new EventEnrollmentWaitlist();
        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('findOneByEventSessionAndEmployee')
            ->once()
            ->with(999, 123)
            ->andReturn($existingWaitlist);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenNonEmployeeEmailIsAlreadyWaitlisted
    public function testValidateThrowsWhenNonEmployeeEmailIsAlreadyWaitlisted(): void
    {
        $this->expectException(AttendeeAlreadyWaitlistedException::class);

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
        $attendee->setEmail('external.waitlisted@example.com');
        $eventCheckout->addEventCheckoutAttendee($attendee);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('external.waitlisted@example.com', $company)
            ->andReturn(null);

        $existingWaitlist = new EventEnrollmentWaitlist();
        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('findOneByEventSessionAndEmail')
            ->once()
            ->with(999, 'external.waitlisted@example.com')
            ->andReturn($existingWaitlist);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWhenNoExistingWaitlist
    public function testValidateSucceedsWhenNoExistingWaitlist(): void
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

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('findOneByEventSessionAndEmployee')
            ->once()
            ->with(999, 123)
            ->andReturn(null);

        $this->employeeRepository
            ->shouldReceive('findOneMatchingEmailAndCompany')
            ->once()
            ->with('external@example.com', $company)
            ->andReturn(null);

        $this->eventEnrollmentWaitlistRepository
            ->shouldReceive('findOneByEventSessionAndEmail')
            ->once()
            ->with(999, 'external@example.com')
            ->andReturn(null);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion
}

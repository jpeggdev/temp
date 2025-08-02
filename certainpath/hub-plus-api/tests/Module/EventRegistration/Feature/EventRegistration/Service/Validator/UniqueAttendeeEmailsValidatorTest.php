<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DuplicateAttendeeEmailException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\UniqueAttendeeEmailsValidator;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UniqueAttendeeEmailsValidatorTest extends TestCase
{
    private UniqueAttendeeEmailsValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new UniqueAttendeeEmailsValidator();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testValidateSucceedsWithUniqueEmails
    public function testValidateSucceedsWithUniqueEmails(): void
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

        $attendees = [];
        for ($i = 1; $i <= 3; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('getEmail')->andReturn("attendee{$i}@example.com");
            $attendees[] = $attendee;
        }

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWithDuplicateEmails
    public function testValidateThrowsWithDuplicateEmails(): void
    {
        $this->expectException(DuplicateAttendeeEmailException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $attendees = [];

        $attendee1 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee1->shouldReceive('getEmail')->andReturn('unique@example.com');
        $attendees[] = $attendee1;

        $attendee2 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee2->shouldReceive('getEmail')->andReturn('duplicate@example.com');
        $attendees[] = $attendee2;

        $attendee3 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee3->shouldReceive('getEmail')->andReturn('duplicate@example.com'); // Duplicate
        $attendees[] = $attendee3;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithSomeNullEmails
    public function testValidateSucceedsWithSomeNullEmails(): void
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

        $attendees = [];

        $attendee1 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee1->shouldReceive('getEmail')->andReturn('valid@example.com');
        $attendees[] = $attendee1;

        $attendee2 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee2->shouldReceive('getEmail')->andReturn(null);
        $attendees[] = $attendee2;

        $attendee3 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee3->shouldReceive('getEmail')->andReturn(null);
        $attendees[] = $attendee3;

        $attendee4 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee4->shouldReceive('getEmail')->andReturn('another@example.com');
        $attendees[] = $attendee4;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWithDuplicateEmailsAmongManyAttendees
    public function testValidateThrowsWithDuplicateEmailsAmongManyAttendees(): void
    {
        $this->expectException(DuplicateAttendeeEmailException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $attendees = [];
        for ($i = 1; $i <= 10; ++$i) {
            $attendee = \Mockery::mock(EventCheckoutAttendee::class);
            $attendee->shouldReceive('getEmail')->andReturn("attendee{$i}@example.com");
            $attendees[] = $attendee;
        }

        $duplicateAttendee = \Mockery::mock(EventCheckoutAttendee::class);
        $duplicateAttendee->shouldReceive('getEmail')->andReturn('attendee5@example.com');
        $attendees[] = $duplicateAttendee;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithEmptyAttendeeList
    public function testValidateSucceedsWithEmptyAttendeeList(): void
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

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection([]));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithCaseSensitiveEmails
    public function testValidateSucceedsWithCaseSensitiveEmails(): void
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

        $attendees = [];

        $attendee1 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee1->shouldReceive('getEmail')->andReturn('User@example.com');
        $attendees[] = $attendee1;

        $attendee2 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee2->shouldReceive('getEmail')->andReturn('user@example.com');
        $attendees[] = $attendee2;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWithWhitespaceEmails
    public function testValidateThrowsWithWhitespaceEmails(): void
    {
        $this->expectException(DuplicateAttendeeEmailException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $attendees = [];

        $attendee1 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee1->shouldReceive('getEmail')->andReturn('user@example.com');
        $attendees[] = $attendee1;

        $attendee2 = \Mockery::mock(EventCheckoutAttendee::class);
        $attendee2->shouldReceive('getEmail')->andReturn(' user@example.com '); // Has whitespace
        $attendees[] = $attendee2;

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventCheckoutAttendees')
            ->andReturn(new ArrayCollection($attendees));

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion
}

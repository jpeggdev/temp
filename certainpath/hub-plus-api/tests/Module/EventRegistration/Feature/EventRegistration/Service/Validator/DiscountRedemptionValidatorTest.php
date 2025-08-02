<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventDiscount;
use App\Entity\EventEventDiscount;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountCodeExpiredException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountCodeNotYetActiveException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountNotValidForEventException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\DiscountReachedMaxUsageException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\InvalidDiscountCodeException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\MinimumPurchaseNotMetException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\DiscountRedemptionValidator;
use App\Repository\EventDiscountRepository;
use App\Repository\InvoiceLineItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscountRedemptionValidatorTest extends TestCase
{
    private EventDiscountRepository|Mockery\MockInterface $eventDiscountRepository;
    private InvoiceLineItemRepository|Mockery\MockInterface $invoiceLineItemRepository;
    private DiscountRedemptionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventDiscountRepository = \Mockery::mock(EventDiscountRepository::class);
        $this->invoiceLineItemRepository = \Mockery::mock(InvoiceLineItemRepository::class);
        $this->validator = new DiscountRedemptionValidator(
            $this->eventDiscountRepository,
            $this->invoiceLineItemRepository
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testValidateSucceedsWithNoDiscountCode
    public function testValidateSucceedsWithNoDiscountCode(): void
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
        $eventCheckout = new EventCheckout();

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWhenNoEvent
    public function testValidateThrowsWhenNoEvent(): void
    {
        $this->expectException(NoEventFoundException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'DISCOUNT'
        );

        $company = new Company();
        $employee = new Employee();

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn(null);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWithInvalidDiscountCode
    public function testValidateThrowsWithInvalidDiscountCode(): void
    {
        $this->expectException(InvalidDiscountCodeException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'INVALID'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('INVALID')
            ->andReturn(null);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWithInactiveDiscountCode
    public function testValidateThrowsWithInactiveDiscountCode(): void
    {
        $this->expectException(InvalidDiscountCodeException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'INACTIVE'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(false);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('INACTIVE')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenDiscountStartDateIsInFuture
    public function testValidateThrowsWhenDiscountStartDateIsInFuture(): void
    {
        $this->expectException(DiscountCodeNotYetActiveException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'FUTURE'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $startDate = new \DateTimeImmutable('+1 day');

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn($startDate);
        $discount->shouldReceive('getEndDate')->andReturn(null);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('FUTURE')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWithExpiredDiscountCode
    public function testValidateThrowsWithExpiredDiscountCode(): void
    {
        $this->expectException(DiscountCodeExpiredException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'EXPIRED'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $startDate = new \DateTimeImmutable('-2 days');
        $endDate = new \DateTimeImmutable('-1 day');

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn($startDate);
        $discount->shouldReceive('getEndDate')->andReturn($endDate);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('EXPIRED')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenDiscountNotValidForEvent
    public function testValidateThrowsWhenDiscountNotValidForEvent(): void
    {
        $this->expectException(DiscountNotValidForEventException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'WRONGEVENT'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getId')->andReturn(123);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $otherEvent = \Mockery::mock(Event::class);
        $otherEvent->shouldReceive('getId')->andReturn(456);

        $mapping = \Mockery::mock(EventEventDiscount::class);
        $mapping->shouldReceive('getEvent')->andReturn($otherEvent);

        $mappings = new ArrayCollection([$mapping]);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn(null);
        $discount->shouldReceive('getEndDate')->andReturn(null);
        $discount->shouldReceive('getEventEventDiscounts')->andReturn($mappings);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('WRONGEVENT')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenDiscountReachedMaxUsage
    public function testValidateThrowsWhenDiscountReachedMaxUsage(): void
    {
        $this->expectException(DiscountReachedMaxUsageException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'MAXED'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn(null);
        $discount->shouldReceive('getEndDate')->andReturn(null);
        $discount->shouldReceive('getEventEventDiscounts')->andReturn(new ArrayCollection([]));
        $discount->shouldReceive('getMaximumUses')->andReturn(10);
        $discount->shouldReceive('getCode')->andReturn('MAXED');

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('MAXED')
            ->andReturn($discount);

        $this->invoiceLineItemRepository
            ->shouldReceive('countInvoiceLineItemsByDiscountCode')
            ->once()
            ->with('MAXED')
            ->andReturn(10);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenMinimumPurchaseNotMet
    public function testValidateThrowsWhenMinimumPurchaseNotMet(): void
    {
        $this->expectException(MinimumPurchaseNotMetException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'MINPURCHASE'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $attendee = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn(null);
        $discount->shouldReceive('getEndDate')->andReturn(null);
        $discount->shouldReceive('getEventEventDiscounts')->andReturn(new ArrayCollection([]));
        $discount->shouldReceive('getMaximumUses')->andReturn(null);
        $discount->shouldReceive('getMinimumPurchaseAmount')->andReturn(100.0);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('MINPURCHASE')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithValidDiscount
    public function testValidateSucceedsWithValidDiscount(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'VALID'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getId')->andReturn(123);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $attendee1 = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee1);
        $attendee2 = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee2);

        $mapping = \Mockery::mock(EventEventDiscount::class);
        $mapping->shouldReceive('getEvent')->andReturn($event);

        $mappings = new ArrayCollection([$mapping]);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn(new \DateTimeImmutable('-1 day'));
        $discount->shouldReceive('getEndDate')->andReturn(new \DateTimeImmutable('+1 day'));
        $discount->shouldReceive('getEventEventDiscounts')->andReturn($mappings);
        $discount->shouldReceive('getMaximumUses')->andReturn(10);
        $discount->shouldReceive('getCode')->andReturn('VALID');
        $discount->shouldReceive('getMinimumPurchaseAmount')->andReturn(50.0);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('VALID')
            ->andReturn($discount);

        $this->invoiceLineItemRepository
            ->shouldReceive('countInvoiceLineItemsByDiscountCode')
            ->once()
            ->with('VALID')
            ->andReturn(5);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithDiscountForAnyEvent
    public function testValidateSucceedsWithDiscountForAnyEvent(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'ANYEVENT'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $attendee1 = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee1);
        $attendee2 = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee2);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn(null);
        $discount->shouldReceive('getEndDate')->andReturn(null);
        $discount->shouldReceive('getEventEventDiscounts')->andReturn(new ArrayCollection([]));
        $discount->shouldReceive('getMaximumUses')->andReturn(null);
        $discount->shouldReceive('getMinimumPurchaseAmount')->andReturn(null);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('ANYEVENT')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWhenMaxUsesIsNull
    public function testValidateSucceedsWhenMaxUsesIsNull(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'UNLIMITED'
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        $attendee1 = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee1);
        $attendee2 = new EventCheckoutAttendee();
        $eventCheckout->addEventCheckoutAttendee($attendee2);

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('isActive')->andReturn(true);
        $discount->shouldReceive('getStartDate')->andReturn(null);
        $discount->shouldReceive('getEndDate')->andReturn(null);
        $discount->shouldReceive('getEventEventDiscounts')->andReturn(new ArrayCollection([]));
        $discount->shouldReceive('getMaximumUses')->andReturn(null);
        $discount->shouldReceive('getMinimumPurchaseAmount')->andReturn(null);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('UNLIMITED')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion
}

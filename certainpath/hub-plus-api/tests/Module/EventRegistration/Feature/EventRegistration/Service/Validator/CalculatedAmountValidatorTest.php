<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\DiscountType;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventCheckoutAttendee;
use App\Entity\EventDiscount;
use App\Entity\EventSession;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoEventSessionFoundException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\PaymentAmountMismatchException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\CalculatedAmountValidator;
use App\Repository\EventDiscountRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class CalculatedAmountValidatorTest extends TestCase
{
    private EventDiscountRepository|Mockery\MockInterface $eventDiscountRepository;
    private CalculatedAmountValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventDiscountRepository = \Mockery::mock(EventDiscountRepository::class);
        $this->validator = new CalculatedAmountValidator($this->eventDiscountRepository);
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
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
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

    // region testValidateWithVariousPriceCalculations
    /**
     * @dataProvider priceCalculationProvider
     */
    public function testValidateWithVariousPriceCalculations(float $eventPrice, float $amount, int $attendeeCount): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $amount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithVouchers
    /**
     * @dataProvider voucherCalculationProvider
     */
    public function testValidateSucceedsWithVouchers(
        float $eventPrice,
        int $attendeeCount,
        int $voucherCount,
        float $expectedAmount,
    ): void {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $expectedAmount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: $voucherCount,
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithPercentageDiscount
    /**
     * @dataProvider percentageDiscountProvider
     */
    public function testValidateSucceedsWithPercentageDiscount(
        float $eventPrice,
        int $attendeeCount,
        float $discountPercentage,
        float $expectedAmount,
    ): void {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $expectedAmount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'DISCOUNT',
            discountAmount: $discountPercentage,
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $discountType = \Mockery::mock(DiscountType::class);
        $discountType->shouldReceive('getName')->andReturn('percentage');

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('getDiscountType')->andReturn($discountType);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('DISCOUNT')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithZeroPercentDiscount
    public function testValidateSucceedsWithZeroPercentDiscount(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'DISCOUNT',
            discountAmount: 0.0,
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < 2; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithFixedDiscount
    /**
     * @dataProvider fixedDiscountProvider
     */
    public function testValidateSucceedsWithFixedDiscount(
        float $eventPrice,
        int $attendeeCount,
        float $discountAmount,
        float $expectedAmount,
    ): void {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $expectedAmount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            discountCode: 'FIXEDDISCOUNT',
            discountAmount: $discountAmount,
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $discountType = \Mockery::mock(DiscountType::class);
        $discountType->shouldReceive('getName')->andReturn('fixed_amount');

        $discount = \Mockery::mock(EventDiscount::class);
        $discount->shouldReceive('getDiscountType')->andReturn($discountType);

        $this->eventDiscountRepository
            ->shouldReceive('findOneByCode')
            ->once()
            ->with('FIXEDDISCOUNT')
            ->andReturn($discount);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithAdminPercentageDiscount
    /**
     * @dataProvider adminPercentageDiscountProvider
     */
    public function testValidateSucceedsWithAdminPercentageDiscount(
        float $eventPrice,
        int $attendeeCount,
        float $adminDiscountValue,
        float $expectedAmount,
    ): void {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $expectedAmount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'percentage',
            adminDiscountValue: $adminDiscountValue,
            adminDiscountReason: 'Test admin percentage discount',
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithAdminFixedDiscount
    /**
     * @dataProvider adminFixedDiscountProvider
     */
    public function testValidateSucceedsWithAdminFixedDiscount(
        float $eventPrice,
        int $attendeeCount,
        float $adminDiscountValue,
        float $expectedAmount,
    ): void {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $expectedAmount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'fixed_amount',
            adminDiscountValue: $adminDiscountValue,
            adminDiscountReason: 'Test admin fixed discount',
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithZeroAdminDiscount
    public function testValidateSucceedsWithZeroAdminDiscount(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'percentage',
            adminDiscountValue: 0.0,
            adminDiscountReason: 'Test zero admin discount',
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn(50.0);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < 2; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWhenAmountMismatch
    /**
     * @dataProvider amountMismatchProvider
     */
    public function testValidateThrowsWhenAmountMismatch(
        float $eventPrice,
        int $attendeeCount,
        float $incorrectAmount,
    ): void {
        $correctAmount = $eventPrice * $attendeeCount;

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $incorrectAmount, // This is intentionally wrong
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        $this->expectException(PaymentAmountMismatchException::class);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithComplexDiscounts
    /**
     * @dataProvider complexDiscountCombinationsProvider
     */
    public function testValidateSucceedsWithComplexDiscounts(
        float $eventPrice,
        int $attendeeCount,
        int $voucherCount,
        string $discountType,
        float $discountAmount,
        string $adminDiscountType,
        float $adminDiscountValue,
        float $expectedAmount,
    ): void {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: $expectedAmount,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: $voucherCount,
            discountCode: $discountAmount > 0 ? 'COMPLEX_DISCOUNT' : null,
            discountAmount: $discountAmount,
            adminDiscountType: $adminDiscountType ?: null,
            adminDiscountValue: $adminDiscountValue,
            adminDiscountReason: $adminDiscountValue > 0 ? 'Complex discount test' : null,
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('getEventPrice')->andReturn($eventPrice);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = new EventCheckout();
        $eventCheckout->setEventSession($eventSession);

        for ($i = 0; $i < $attendeeCount; ++$i) {
            $attendee = new EventCheckoutAttendee();
            $attendee->setEmail("attendee{$i}@example.com");
            $attendee->setIsWaitlist(false);
            $eventCheckout->addEventCheckoutAttendee($attendee);
        }

        if ($discountAmount > 0) {
            $discountTypeEntity = \Mockery::mock(DiscountType::class);
            $discountTypeEntity->shouldReceive('getName')->andReturn($discountType);

            $discount = \Mockery::mock(EventDiscount::class);
            $discount->shouldReceive('getDiscountType')->andReturn($discountTypeEntity);

            $this->eventDiscountRepository
                ->shouldReceive('findOneByCode')
                ->once()
                ->with('COMPLEX_DISCOUNT')
                ->andReturn($discount);
        }

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region voucherCalculationProvider
    public function voucherCalculationProvider(): array
    {
        return [
            'single voucher for single attendee' => [50.0, 1, 1, 0.0],
            'single voucher for two attendees' => [50.0, 2, 1, 50.0],
            'two vouchers for two attendees' => [50.0, 2, 2, 0.0],
            'excess vouchers (more than attendees)' => [50.0, 2, 3, 0.0],
            'partial coverage (2 vouchers for 3 attendees)' => [50.0, 3, 2, 50.0],
            'no vouchers' => [50.0, 2, 0, 100.0],
            'decimal price with voucher' => [24.99, 3, 1, 49.98],
            'large number of attendees and vouchers' => [19.95, 10, 5, 99.75],
            'full coverage with odd pricing' => [33.33, 3, 3, 0.0],
            'single voucher with zero-cost event' => [0.0, 2, 1, 0.0],
        ];
    }
    // endregion

    // region priceCalculationProvider
    public function priceCalculationProvider(): array
    {
        return [
            'whole numbers' => [50.0, 100.0, 2],
            'single decimal place' => [24.5, 73.5, 3],
            'standard two decimal places' => [19.99, 59.97, 3],
            'common price point' => [33.33, 99.99, 3],
            'typical event price' => [45.67, 91.34, 2],
            'price with trailing zeroes' => [100.00, 300.00, 3],
            'very small price' => [0.01, 0.03, 3],
            'odd number of attendees with decimal price' => [19.95, 99.75, 5],
            'large number of attendees' => [1.23, 12.30, 10],
            'price ending in .99' => [10.99, 32.97, 3],
            'zero-cost event' => [0.0, 0.0, 4],
        ];
    }
    // endregion

    // region percentageDiscountProvider
    public function percentageDiscountProvider(): array
    {
        return [
            'small discount (10%)' => [50.0, 2, 10.0, 90.0],
            'standard discount (20%)' => [50.0, 2, 20.0, 80.0],
            'large discount (50%)' => [50.0, 2, 50.0, 50.0],
            'full discount (100%)' => [50.0, 2, 100.0, 0.0],
            'decimal discount (12.5%)' => [40.0, 2, 12.5, 70.0],
            'odd price with discount' => [33.33, 3, 15.0, 84.99],
            'multiple attendees with large discount' => [25.0, 4, 75.0, 25.0],
            'single attendee with discount' => [99.99, 1, 30.0, 69.99],
            'tiny discount (1%)' => [100.0, 2, 1.0, 198.0],
        ];
    }
    // endregion

    // region fixedDiscountProvider
    public function fixedDiscountProvider(): array
    {
        return [
            'small fixed discount' => [50.0, 2, 10.0, 90.0],
            'medium fixed discount' => [50.0, 2, 25.0, 75.0],
            'large fixed discount' => [50.0, 2, 75.0, 25.0],
            'full coverage discount' => [50.0, 2, 100.0, 0.0],
            'excess discount (more than total)' => [50.0, 2, 150.0, 0.0],
            'decimal discount amount' => [40.0, 2, 12.5, 67.5],
            'small discount on large total' => [33.33, 3, 5.0, 94.99],
            'large discount on large total' => [25.0, 4, 75.0, 25.0],
            'discount equal to single item' => [99.99, 3, 99.99, 199.98],
            'very small discount' => [20.0, 2, 0.01, 39.99],
        ];
    }
    // endregion

    // region adminPercentageDiscountProvider
    public function adminPercentageDiscountProvider(): array
    {
        return [
            'small admin percentage discount (10%)' => [50.0, 2, 10.0, 90.0],
            'standard admin percentage discount (20%)' => [50.0, 2, 20.0, 80.0],
            'large admin percentage discount (50%)' => [50.0, 2, 50.0, 50.0],
            'full admin percentage discount (100%)' => [50.0, 2, 100.0, 0.0],
            'decimal admin percentage discount (12.5%)' => [40.0, 2, 12.5, 70.0],
            'odd price with admin percentage discount' => [33.33, 3, 15.0, 84.99],
            'multiple attendees with admin percentage discount' => [25.0, 4, 75.0, 25.0],
            'single attendee with admin percentage discount' => [99.99, 1, 30.0, 69.99],
            'tiny admin percentage discount (1%)' => [100.0, 2, 1.0, 198.0],
        ];
    }
    // endregion

    // region adminFixedDiscountProvider
    public function adminFixedDiscountProvider(): array
    {
        return [
            'small admin fixed discount' => [50.0, 2, 10.0, 90.0],
            'medium admin fixed discount' => [50.0, 2, 25.0, 75.0],
            'large admin fixed discount' => [50.0, 2, 75.0, 25.0],
            'full coverage admin fixed discount' => [50.0, 2, 100.0, 0.0],
            'excess admin fixed discount (more than total)' => [50.0, 2, 150.0, 0.0],
            'decimal admin fixed discount amount' => [40.0, 2, 12.5, 67.5],
            'small admin fixed discount on large total' => [33.33, 3, 5.0, 94.99],
            'large admin fixed discount on large total' => [25.0, 4, 75.0, 25.0],
            'admin fixed discount equal to single item' => [99.99, 3, 99.99, 199.98],
            'very small admin fixed discount' => [20.0, 2, 0.01, 39.99],
        ];
    }
    // endregion

    // region amountMismatchProvider
    public function amountMismatchProvider(): array
    {
        return [
            'amount too low' => [50.0, 2, 95.0],
            'amount too high' => [50.0, 2, 105.0],
            'amount significantly different' => [50.0, 2, 200.0],
            'amount slightly off' => [49.99, 3, 150.0],
            'zero amount for paid event' => [25.0, 4, 0.0],
            'non-zero amount for free event' => [0.0, 3, 10.0],
            'decimal precision issue' => [33.33, 3, 100.01], // Should be 99.99
            'wrong calculation entirely' => [19.95, 5, 89.75], // Should be 99.75
        ];
    }
    // endregion

    // region complexDiscountCombinationsProvider
    public function complexDiscountCombinationsProvider(): array
    {
        return [
            'vouchers and percentage discount' => [
                50.0,
                4,
                1,
                'percentage',
                20.0,
                '',
                0.0,
                110.0,
            ],
            'vouchers and fixed discount' => [
                50.0,
                4,
                1,
                'fixed_amount',
                25.0,
                '',
                0.0,
                125.0,
            ],
            'admin percentage and regular percentage discount' => [
                50.0,
                3,
                0,
                'percentage',
                10.0,
                'percentage',
                15.0,
                112.5,
            ],
            'admin fixed and regular fixed discount' => [
                50.0,
                3,
                0,
                'fixed_amount',
                20.0,
                'fixed_amount',
                30.0,
                100.0,
            ],
            'vouchers, admin percentage, and regular fixed discount' => [
                40.0,
                5,
                2,
                'fixed_amount',
                15.0,
                'percentage',
                25.0,
                55.0,
            ],
            'vouchers, admin fixed, and regular percentage discount' => [
                40.0,
                5,
                2,
                'percentage',
                25.0,
                'fixed_amount',
                20.0,
                50.0,
            ],
            'full combination with percentage discounts' => [
                60.0,
                4,
                1,
                'percentage',
                10.0,
                'percentage',
                20.0,
                108.0,
            ],
            'full combination with fixed discounts' => [
                60.0,
                4,
                1,
                'fixed_amount',
                25.0,
                'fixed_amount',
                35.0,
                120.0,
            ],
            'multiple discounts resulting in zero payment' => [
                25.0,
                3,
                1,
                'percentage',
                50.0,
                'fixed_amount',
                25.0,
                0.0,
            ],
            'complex combination with odd pricing' => [
                33.33,
                3,
                1,
                'percentage',
                15.0,
                'percentage',
                10.0,
                41.66,
            ],
        ];
    }
    // endregion
}

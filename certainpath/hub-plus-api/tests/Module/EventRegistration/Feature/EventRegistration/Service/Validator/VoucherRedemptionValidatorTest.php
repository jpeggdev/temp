<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\Event;
use App\Entity\EventCheckout;
use App\Entity\EventSession;
use App\Entity\EventVoucher;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\EventNotEligibleForVoucherException;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\InsufficientVoucherSeatsException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\VoucherRedemptionValidator;
use App\Repository\CreditMemoLineItemRepository;
use App\Repository\EventVoucherRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class VoucherRedemptionValidatorTest extends TestCase
{
    private EventVoucherRepository|Mockery\MockInterface $eventVoucherRepository;
    private CreditMemoLineItemRepository|Mockery\MockInterface $creditMemoLineItemRepository;
    private VoucherRedemptionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventVoucherRepository = \Mockery::mock(EventVoucherRepository::class);
        $this->creditMemoLineItemRepository = \Mockery::mock(CreditMemoLineItemRepository::class);
        $this->validator = new VoucherRedemptionValidator(
            $this->eventVoucherRepository,
            $this->creditMemoLineItemRepository
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testValidateSucceedsWithZeroVoucherQuantity
    public function testValidateSucceedsWithZeroVoucherQuantity(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 0
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = \Mockery::mock(EventCheckout::class);

        // Repository methods should not be called
        $this->eventVoucherRepository->shouldNotReceive('findAllByCompany');
        $this->creditMemoLineItemRepository->shouldNotReceive('countVoucherLineItemsForCompany');

        // Validation should pass with zero vouchers
        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithNullVoucherQuantity
    public function testValidateSucceedsWithNullVoucherQuantity(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: null
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = \Mockery::mock(EventCheckout::class);

        $this->eventVoucherRepository->shouldNotReceive('findAllByCompany');
        $this->creditMemoLineItemRepository->shouldNotReceive('countVoucherLineItemsForCompany');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWhenEventNotEligibleForVoucher
    public function testValidateThrowsWhenEventNotEligibleForVoucher(): void
    {
        $this->expectException(EventNotEligibleForVoucherException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 2
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(false);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $this->eventVoucherRepository->shouldNotReceive('findAllByCompany');
        $this->creditMemoLineItemRepository->shouldNotReceive('countVoucherLineItemsForCompany');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWhenEventSessionOrEventIsNull
    public function testValidateThrowsWhenEventSessionOrEventIsNull(): void
    {
        $this->expectException(EventNotEligibleForVoucherException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 2
        );

        $company = new Company();
        $employee = new Employee();

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn(null);

        $this->eventVoucherRepository->shouldNotReceive('findAllByCompany');
        $this->creditMemoLineItemRepository->shouldNotReceive('countVoucherLineItemsForCompany');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithSufficientVoucherSeats
    public function testValidateSucceedsWithSufficientVoucherSeats(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 3
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $voucher = \Mockery::mock(EventVoucher::class);
        $voucher->shouldReceive('isActive')->andReturn(true);
        $voucher->shouldReceive('getStartDate')->andReturn(null);
        $voucher->shouldReceive('getEndDate')->andReturn(null);
        $voucher->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(1);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWithInsufficientVoucherSeats
    public function testValidateThrowsWithInsufficientVoucherSeats(): void
    {
        $this->expectException(InsufficientVoucherSeatsException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 5
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $voucher = \Mockery::mock(EventVoucher::class);
        $voucher->shouldReceive('isActive')->andReturn(true);
        $voucher->shouldReceive('getStartDate')->andReturn(null);
        $voucher->shouldReceive('getEndDate')->andReturn(null);
        $voucher->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(2);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWithInactiveVouchers
    public function testValidateThrowsWithInactiveVouchers(): void
    {
        $this->expectException(InsufficientVoucherSeatsException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 2
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $voucher = \Mockery::mock(EventVoucher::class);
        $voucher->shouldReceive('isActive')->andReturn(false);
        $voucher->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWithExpiredVouchers
    public function testValidateThrowsWithExpiredVouchers(): void
    {
        $this->expectException(InsufficientVoucherSeatsException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 2
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $pastDate = new \DateTimeImmutable('-1 day');
        $voucher = \Mockery::mock(EventVoucher::class);
        $voucher->shouldReceive('isActive')->andReturn(true);
        $voucher->shouldReceive('getStartDate')->andReturn(null);
        $voucher->shouldReceive('getEndDate')->andReturn($pastDate);
        $voucher->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateThrowsWithFutureVouchers
    public function testValidateThrowsWithFutureVouchers(): void
    {
        $this->expectException(InsufficientVoucherSeatsException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 2
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $futureDate = new \DateTimeImmutable('+1 day');
        $voucher = \Mockery::mock(EventVoucher::class);
        $voucher->shouldReceive('isActive')->andReturn(true);
        $voucher->shouldReceive('getStartDate')->andReturn($futureDate);
        $voucher->shouldReceive('getEndDate')->andReturn(null);
        $voucher->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWithMixedVouchers
    public function testValidateSucceedsWithMixedVouchers(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 2
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $validVoucher = \Mockery::mock(EventVoucher::class);
        $validVoucher->shouldReceive('isActive')->andReturn(true);
        $validVoucher->shouldReceive('getStartDate')->andReturn(null);
        $validVoucher->shouldReceive('getEndDate')->andReturn(null);
        $validVoucher->shouldReceive('getTotalSeats')->andReturn(3);

        $inactiveVoucher = \Mockery::mock(EventVoucher::class);
        $inactiveVoucher->shouldReceive('isActive')->andReturn(false);
        $inactiveVoucher->shouldReceive('getTotalSeats')->andReturn(5);

        $pastDate = new \DateTimeImmutable('-1 day');
        $expiredVoucher = \Mockery::mock(EventVoucher::class);
        $expiredVoucher->shouldReceive('isActive')->andReturn(true);
        $expiredVoucher->shouldReceive('getStartDate')->andReturn(null);
        $expiredVoucher->shouldReceive('getEndDate')->andReturn($pastDate);
        $expiredVoucher->shouldReceive('getTotalSeats')->andReturn(4);

        $futureDate = new \DateTimeImmutable('+1 day');
        $futureVoucher = \Mockery::mock(EventVoucher::class);
        $futureVoucher->shouldReceive('isActive')->andReturn(true);
        $futureVoucher->shouldReceive('getStartDate')->andReturn($futureDate);
        $futureVoucher->shouldReceive('getEndDate')->andReturn(null);
        $futureVoucher->shouldReceive('getTotalSeats')->andReturn(6);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$validVoucher, $inactiveVoucher, $expiredVoucher, $futureVoucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(1);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
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
            voucherQuantity: 5
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $voucher = \Mockery::mock(EventVoucher::class);
        $voucher->shouldReceive('isActive')->andReturn(true);
        $voucher->shouldReceive('getStartDate')->andReturn(null);
        $voucher->shouldReceive('getEndDate')->andReturn(null);
        $voucher->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(0);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWithMultipleValidVouchers
    public function testValidateSucceedsWithMultipleValidVouchers(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            voucherQuantity: 7
        );

        $company = new Company();
        $employee = new Employee();

        $event = \Mockery::mock(Event::class);
        $event->shouldReceive('isVoucherEligible')->andReturn(true);

        $eventSession = \Mockery::mock(EventSession::class);
        $eventSession->shouldReceive('getEvent')->andReturn($event);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getEventSession')->andReturn($eventSession);

        $voucher1 = \Mockery::mock(EventVoucher::class);
        $voucher1->shouldReceive('isActive')->andReturn(true);
        $voucher1->shouldReceive('getStartDate')->andReturn(null);
        $voucher1->shouldReceive('getEndDate')->andReturn(null);
        $voucher1->shouldReceive('getTotalSeats')->andReturn(3);

        $voucher2 = \Mockery::mock(EventVoucher::class);
        $voucher2->shouldReceive('isActive')->andReturn(true);
        $voucher2->shouldReceive('getStartDate')->andReturn(null);
        $voucher2->shouldReceive('getEndDate')->andReturn(null);
        $voucher2->shouldReceive('getTotalSeats')->andReturn(5);

        $this->eventVoucherRepository
            ->shouldReceive('findAllByCompany')
            ->once()
            ->with($company)
            ->andReturn([$voucher1, $voucher2]);

        $this->creditMemoLineItemRepository
            ->shouldReceive('countVoucherLineItemsForCompany')
            ->once()
            ->with($company)
            ->andReturn(1);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion
}

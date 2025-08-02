<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Enum\EventCheckoutSessionStatus;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor\FinalizeCheckoutMetadataPostProcessor;
use App\Repository\EventCheckoutRepository;
use Mockery;
use PHPUnit\Framework\TestCase;

class FinalizeCheckoutMetadataPostProcessorTest extends TestCase
{
    private EventCheckoutRepository|Mockery\MockInterface $eventCheckoutRepository;
    private FinalizeCheckoutMetadataPostProcessor $postProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventCheckoutRepository = \Mockery::mock(EventCheckoutRepository::class);
        $this->postProcessor = new FinalizeCheckoutMetadataPostProcessor(
            $this->eventCheckoutRepository
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testPostProcessSetsStatusAndAmountAndFinalizedAt
    public function testPostProcessSetsStatusAndAmountAndFinalizedAt(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 123.45,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getConfirmationNumber')->andReturn('EXISTING-CN');
        $eventCheckout->shouldReceive('setStatus')
            ->once()
            ->with(EventCheckoutSessionStatus::COMPLETED);
        $eventCheckout->shouldReceive('setAmount')
            ->once()
            ->with('123.45');
        $eventCheckout->shouldReceive('setFinalizedAt')
            ->once()
            ->with(\Mockery::type(\DateTimeImmutable::class));
        $eventCheckout->shouldNotReceive('setConfirmationNumber');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessGeneratesConfirmationNumberWhenNull
    public function testPostProcessGeneratesConfirmationNumberWhenNull(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 123.45,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
        );

        $company = new Company();
        $employee = new Employee();
        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getConfirmationNumber')->andReturn(null);
        $eventCheckout->shouldReceive('setStatus')->once();
        $eventCheckout->shouldReceive('setAmount')->once();
        $eventCheckout->shouldReceive('setFinalizedAt')->once();
        $eventCheckout->shouldReceive('setConfirmationNumber')
            ->once()
            ->with(\Mockery::type('string'));

        $this->eventCheckoutRepository
            ->shouldReceive('findOneByConfirmationNumber')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn(null);

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessDoesNotChangeExistingConfirmationNumber
    public function testPostProcessDoesNotChangeExistingConfirmationNumber(): void
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

        $existingConfirmationNumber = 'CN-ABCDEF123456';

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getConfirmationNumber')->andReturn($existingConfirmationNumber);
        $eventCheckout->shouldReceive('setStatus')->once();
        $eventCheckout->shouldReceive('setAmount')->once();
        $eventCheckout->shouldReceive('setFinalizedAt')->once();
        $eventCheckout->shouldNotReceive('setConfirmationNumber');

        $this->eventCheckoutRepository->shouldNotReceive('findOneByConfirmationNumber');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessGeneratesUniqueConfirmationNumber
    public function testPostProcessGeneratesUniqueConfirmationNumber(): void
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
        $eventCheckout->shouldReceive('getConfirmationNumber')->andReturn(null);
        $eventCheckout->shouldReceive('setStatus')->once();
        $eventCheckout->shouldReceive('setAmount')->once();
        $eventCheckout->shouldReceive('setFinalizedAt')->once();
        $eventCheckout->shouldReceive('setConfirmationNumber')
            ->once()
            ->with(\Mockery::type('string'));

        $existingCheckout = \Mockery::mock(EventCheckout::class);

        $this->eventCheckoutRepository
            ->shouldReceive('findOneByConfirmationNumber')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn($existingCheckout)
            ->globally()
            ->ordered();

        $this->eventCheckoutRepository
            ->shouldReceive('findOneByConfirmationNumber')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn(null)
            ->globally()
            ->ordered();

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

        $eventCheckout = \Mockery::mock(EventCheckout::class);
        $eventCheckout->shouldReceive('getConfirmationNumber')->andReturn(null);
        $eventCheckout->shouldReceive('setStatus')->once();
        $eventCheckout->shouldReceive('setAmount')->once();
        $eventCheckout->shouldReceive('setFinalizedAt')->once();
        $eventCheckout->shouldReceive('setConfirmationNumber')->once();

        $this->eventCheckoutRepository
            ->shouldReceive('findOneByConfirmationNumber')
            ->once()
            ->andReturn(null);

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, null);

        $this->assertTrue(true);
    }
    // endregion

    // region testConfirmationNumberFormat
    public function testConfirmationNumberFormat(): void
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
        $eventCheckout->shouldReceive('getConfirmationNumber')->andReturn(null);
        $eventCheckout->shouldReceive('setStatus')->once();
        $eventCheckout->shouldReceive('setAmount')->once();
        $eventCheckout->shouldReceive('setFinalizedAt')->once();

        $eventCheckout->shouldReceive('setConfirmationNumber')
            ->once()
            ->with(\Mockery::on(function ($confirmationNumber) {
                return 1 === preg_match('/^CN-[0-9A-F]{8}$/', $confirmationNumber);
            }));

        $this->eventCheckoutRepository
            ->shouldReceive('findOneByConfirmationNumber')
            ->once()
            ->andReturn(null);

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion
}

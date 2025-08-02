<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\PaymentProfile;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor\SavePaymentInformationPostProcessor;
use App\Repository\PaymentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class SavePaymentInformationPostProcessorTest extends TestCase
{
    private EntityManagerInterface|Mockery\MockInterface $entityManager;
    private PaymentProfileRepository|Mockery\MockInterface $paymentProfileRepository;
    private SavePaymentInformationPostProcessor $postProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->paymentProfileRepository = \Mockery::mock(PaymentProfileRepository::class);
        $this->postProcessor = new SavePaymentInformationPostProcessor(
            $this->entityManager,
            $this->paymentProfileRepository
        );
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testPostProcessDoesNothingWhenChargeResponseIsNull
    public function testPostProcessDoesNothingWhenChargeResponseIsNull(): void
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

        $eventCheckout->shouldNotReceive('setAuthnetCustomerProfileId');
        $eventCheckout->shouldNotReceive('setAuthnetPaymentProfileId');
        $eventCheckout->shouldNotReceive('setCardLast4');
        $eventCheckout->shouldNotReceive('setCardType');

        $this->paymentProfileRepository->shouldNotReceive('findOneByEmployeeAndAuthNetProfiles');
        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, null);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessStoresPaymentInfoOnCheckout
    public function testPostProcessStoresPaymentInfoOnCheckout(): void
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

        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);
        $chargeResponse->customerProfileId = '12345';
        $chargeResponse->paymentProfileId = '67890';
        $chargeResponse->accountLast4 = '1234';
        $chargeResponse->accountType = 'Visa';

        $eventCheckout->shouldReceive('setAuthnetCustomerProfileId')
            ->once()
            ->with('12345');
        $eventCheckout->shouldReceive('setAuthnetPaymentProfileId')
            ->once()
            ->with('67890');
        $eventCheckout->shouldReceive('setCardLast4')
            ->once()
            ->with('1234');
        $eventCheckout->shouldReceive('setCardType')
            ->once()
            ->with('Visa');

        $this->paymentProfileRepository
            ->shouldReceive('findOneByEmployeeAndAuthNetProfiles')
            ->once()
            ->with($employee, '12345', '67890')
            ->andReturn(null);

        $this->entityManager
            ->shouldReceive('persist')
            ->once()
            ->withArgs(function (PaymentProfile $paymentProfile) use ($employee) {
                $this->assertSame($employee, $paymentProfile->getEmployee());
                $this->assertSame('12345', $paymentProfile->getAuthnetCustomerId());
                $this->assertSame('67890', $paymentProfile->getAuthnetPaymentProfileId());
                $this->assertSame('1234', $paymentProfile->getCardLast4());
                $this->assertSame('Visa', $paymentProfile->getCardType());

                return true;
            });

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessUpdatesExistingPaymentProfile
    public function testPostProcessUpdatesExistingPaymentProfile(): void
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

        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);
        $chargeResponse->customerProfileId = '12345';
        $chargeResponse->paymentProfileId = '67890';
        $chargeResponse->accountLast4 = '5678';
        $chargeResponse->accountType = 'Mastercard';

        $eventCheckout->shouldReceive('setAuthnetCustomerProfileId')->once();
        $eventCheckout->shouldReceive('setAuthnetPaymentProfileId')->once();
        $eventCheckout->shouldReceive('setCardLast4')->once();
        $eventCheckout->shouldReceive('setCardType')->once();

        $existingProfile = \Mockery::mock(PaymentProfile::class);
        $existingProfile->shouldReceive('getCardLast4')->andReturn('1234');
        $existingProfile->shouldReceive('getCardType')->andReturn('Visa');
        $existingProfile->shouldReceive('setCardLast4')
            ->once()
            ->with('5678');
        $existingProfile->shouldReceive('setCardType')
            ->once()
            ->with('Mastercard');

        $this->paymentProfileRepository
            ->shouldReceive('findOneByEmployeeAndAuthNetProfiles')
            ->once()
            ->with($employee, '12345', '67890')
            ->andReturn($existingProfile);

        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessDoesNotUpdateUnchangedFields
    public function testPostProcessDoesNotUpdateUnchangedFields(): void
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

        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);
        $chargeResponse->customerProfileId = '12345';
        $chargeResponse->paymentProfileId = '67890';
        $chargeResponse->accountLast4 = '1234';
        $chargeResponse->accountType = 'Visa';

        $eventCheckout->shouldReceive('setAuthnetCustomerProfileId')->once();
        $eventCheckout->shouldReceive('setAuthnetPaymentProfileId')->once();
        $eventCheckout->shouldReceive('setCardLast4')->once();
        $eventCheckout->shouldReceive('setCardType')->once();

        $existingProfile = \Mockery::mock(PaymentProfile::class);
        $existingProfile->shouldReceive('getCardLast4')->andReturn('1234');
        $existingProfile->shouldReceive('getCardType')->andReturn('Visa');
        $existingProfile->shouldNotReceive('setCardLast4');
        $existingProfile->shouldNotReceive('setCardType');

        $this->paymentProfileRepository
            ->shouldReceive('findOneByEmployeeAndAuthNetProfiles')
            ->once()
            ->with($employee, '12345', '67890')
            ->andReturn($existingProfile);

        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessHandlesMissingProfileIds
    public function testPostProcessHandlesMissingProfileIds(): void
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

        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);
        $chargeResponse->customerProfileId = null;
        $chargeResponse->paymentProfileId = null;
        $chargeResponse->accountLast4 = '1234';
        $chargeResponse->accountType = 'Visa';

        $eventCheckout->shouldReceive('setAuthnetCustomerProfileId')->once();
        $eventCheckout->shouldReceive('setAuthnetPaymentProfileId')->once();
        $eventCheckout->shouldReceive('setCardLast4')->once();
        $eventCheckout->shouldReceive('setCardType')->once();

        $this->paymentProfileRepository->shouldNotReceive('findOneByEmployeeAndAuthNetProfiles');
        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion

    // region testPostProcessWithNullCardDetails
    public function testPostProcessWithNullCardDetails(): void
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

        $chargeResponse = \Mockery::mock(AuthNetChargeResponseDTO::class);
        $chargeResponse->customerProfileId = '12345';
        $chargeResponse->paymentProfileId = '67890';
        $chargeResponse->accountLast4 = null;
        $chargeResponse->accountType = null;

        $eventCheckout->shouldReceive('setAuthnetCustomerProfileId')->once();
        $eventCheckout->shouldReceive('setAuthnetPaymentProfileId')->once();
        $eventCheckout->shouldReceive('setCardLast4')->once();
        $eventCheckout->shouldReceive('setCardType')->once();

        $existingProfile = \Mockery::mock(PaymentProfile::class);
        $existingProfile->shouldReceive('getCardLast4')->andReturn('1234');
        $existingProfile->shouldReceive('getCardType')->andReturn('Visa');
        $existingProfile->shouldNotReceive('setCardLast4');
        $existingProfile->shouldNotReceive('setCardType');

        $this->paymentProfileRepository
            ->shouldReceive('findOneByEmployeeAndAuthNetProfiles')
            ->once()
            ->with($employee, '12345', '67890')
            ->andReturn($existingProfile);

        $this->entityManager->shouldNotReceive('persist');

        $this->postProcessor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);

        $this->assertTrue(true);
    }
    // endregion
}

<?php

namespace App\Tests\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoPermissionToApplyAdminDiscountException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\RoleForAdminDiscountValidator;
use App\Service\PermissionService;
use Mockery;
use PHPUnit\Framework\TestCase;

class RoleForAdminDiscountValidatorTest extends TestCase
{
    private PermissionService|Mockery\MockInterface $permissionService;
    private RoleForAdminDiscountValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionService = \Mockery::mock(PermissionService::class);
        $this->validator = new RoleForAdminDiscountValidator($this->permissionService);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    // region testValidateSucceedsWhenNoAdminDiscountApplied
    public function testValidateSucceedsWhenNoAdminDiscountApplied(): void
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

        $this->permissionService->shouldNotReceive('hasRole');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWhenAdminDiscountAppliedBySuperAdmin
    public function testValidateSucceedsWhenAdminDiscountAppliedBySuperAdmin(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 80.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'percentage',
            adminDiscountValue: 20.0,
            adminDiscountReason: 'Test discount',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->permissionService
            ->shouldReceive('hasRole')
            ->once()
            ->with($employee, 'ROLE_SUPER_ADMIN')
            ->andReturn(true);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateThrowsWhenAdminDiscountAppliedByNonSuperAdmin
    public function testValidateThrowsWhenAdminDiscountAppliedByNonSuperAdmin(): void
    {
        $this->expectException(NoPermissionToApplyAdminDiscountException::class);

        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 80.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'percentage',
            adminDiscountValue: 20.0,
            adminDiscountReason: 'Test discount',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->permissionService
            ->shouldReceive('hasRole')
            ->once()
            ->with($employee, 'ROLE_SUPER_ADMIN')
            ->andReturn(false);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);
    }
    // endregion

    // region testValidateSucceedsWhenAdminDiscountTypeProvidedButValueIsZero
    public function testValidateSucceedsWhenAdminDiscountTypeProvidedButValueIsZero(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'percentage',
            adminDiscountValue: 0.0,
            adminDiscountReason: 'Zero discount',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->permissionService->shouldNotReceive('hasRole');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWhenAdminDiscountTypeProvidedButValueIsNull
    public function testValidateSucceedsWhenAdminDiscountTypeProvidedButValueIsNull(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: 'percentage',
            adminDiscountValue: null,
            adminDiscountReason: 'Null discount',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->permissionService->shouldNotReceive('hasRole');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateSucceedsWhenAdminDiscountValueProvidedButTypeIsNull
    public function testValidateSucceedsWhenAdminDiscountValueProvidedButTypeIsNull(): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 100.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: null,
            adminDiscountValue: 20.0,
            adminDiscountReason: 'Missing type',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->permissionService->shouldNotReceive('hasRole');

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region testValidateWithVariousDiscountTypes
    /**
     * @dataProvider adminDiscountTypeProvider
     */
    public function testValidateWithVariousDiscountTypes(string $discountType): void
    {
        $dto = new ProcessPaymentRequestDTO(
            dataDescriptor: 'foo',
            dataValue: 'bar',
            amount: 80.0,
            invoiceNumber: 'INV123',
            eventCheckoutSessionUuid: 'some-uuid',
            adminDiscountType: $discountType,
            adminDiscountValue: 20.0,
            adminDiscountReason: 'Test discount',
        );

        $company = new Company();
        $employee = new Employee();
        $eventCheckout = new EventCheckout();

        $this->permissionService
            ->shouldReceive('hasRole')
            ->once()
            ->with($employee, 'ROLE_SUPER_ADMIN')
            ->andReturn(true);

        $this->validator->validate($dto, $eventCheckout, $company, $employee);

        $this->assertTrue(true);
    }
    // endregion

    // region adminDiscountTypeProvider
    public function adminDiscountTypeProvider(): array
    {
        return [
            'percentage discount' => ['percentage'],
            'fixed amount discount' => ['fixed_amount'],
        ];
    }
    // endregion
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\NoPermissionToApplyAdminDiscountException;
use App\Service\PermissionService;

final readonly class RoleForAdminDiscountValidator implements EventCheckoutValidatorInterface
{
    public function __construct(
        private PermissionService $permissionService,
    ) {
    }

    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        if (
            $dto->adminDiscountType
            && $dto->adminDiscountValue
            && $dto->adminDiscountValue > 0
            && !$this->permissionService->hasRole($employee, 'ROLE_SUPER_ADMIN')
        ) {
            throw new NoPermissionToApplyAdminDiscountException('You do not have permission to apply an admin discount.');
        }
    }
}

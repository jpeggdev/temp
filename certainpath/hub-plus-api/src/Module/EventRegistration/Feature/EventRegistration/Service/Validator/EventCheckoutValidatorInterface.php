<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Each "validator" can implement logic that runs before payment is attempted
 * E.g. validating ledgers, enrollments, or discounts.
 */
#[AutoconfigureTag('app.event_checkout_validator')]
interface EventCheckoutValidatorInterface
{
    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void;
}

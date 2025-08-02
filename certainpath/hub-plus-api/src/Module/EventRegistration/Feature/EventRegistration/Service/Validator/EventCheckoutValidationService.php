<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Validator;

use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;

/**
 * Runs a collection of validators in sequence. Each validator implements
 * EventCheckoutValidatorInterface. You no longer need a services.yaml entry.
 */
final readonly class EventCheckoutValidationService
{
    /**
     * @param iterable<EventCheckoutValidatorInterface> $validators
     */
    public function __construct(
        private iterable $validators,
    ) {
    }

    /**
     * Runs each validator in sequence. If any validation fails, it should throw its own exception.
     */
    public function validate(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
    ): void {
        foreach ($this->validators as $validator) {
            $validator->validate($dto, $eventCheckout, $company, $employee);
        }
    }
}

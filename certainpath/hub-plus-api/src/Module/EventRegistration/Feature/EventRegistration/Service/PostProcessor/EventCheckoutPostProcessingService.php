<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;

/**
 * Service that orchestrates post-processing steps in sequence.
 */
final readonly class EventCheckoutPostProcessingService
{
    /**
     * @param iterable<EventCheckoutPostProcessorInterface> $postProcessors
     */
    public function __construct(
        private iterable $postProcessors,
    ) {
    }

    public function postProcess(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): void {
        foreach ($this->postProcessors as $processor) {
            $processor->postProcess($dto, $eventCheckout, $company, $employee, $chargeResponse);
        }
    }
}

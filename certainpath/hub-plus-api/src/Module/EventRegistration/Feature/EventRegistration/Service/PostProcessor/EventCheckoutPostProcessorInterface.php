<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Each "post-processor" can implement logic that runs AFTER payment is successful
 * or zero-amount. E.g. creating ledgers, enrollments, or sending receipts.
 */
#[AutoconfigureTag('app.event_checkout_post_processor')]
interface EventCheckoutPostProcessorInterface
{
    public function postProcess(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): void;
}

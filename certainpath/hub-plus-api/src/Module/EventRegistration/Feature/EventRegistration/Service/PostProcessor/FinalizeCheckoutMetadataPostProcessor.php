<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Enum\EventCheckoutSessionStatus;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Repository\EventCheckoutRepository;

final readonly class FinalizeCheckoutMetadataPostProcessor implements EventCheckoutPostProcessorInterface
{
    public function __construct(
        private EventCheckoutRepository $eventCheckoutRepository,
    ) {
    }

    public function postProcess(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): void {
        $eventCheckout->setStatus(EventCheckoutSessionStatus::COMPLETED);
        $eventCheckout->setAmount((string) $dto->amount);
        $eventCheckout->setFinalizedAt(new \DateTimeImmutable());

        if (null === $eventCheckout->getConfirmationNumber()) {
            $confirmationNumber = $this->generateUniqueConfirmationNumber();
            $eventCheckout->setConfirmationNumber($confirmationNumber);
        }
    }

    private function generateUniqueConfirmationNumber(): string
    {
        do {
            $random = 'CN-'.bin2hex(random_bytes(4));
            $random = strtoupper($random);
            $existing = $this->eventCheckoutRepository->findOneByConfirmationNumber($random);
        } while (null !== $existing);

        return $random;
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Entity\EventCheckout;
use App\Entity\PaymentProfile;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Repository\PaymentProfileRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SavePaymentInformationPostProcessor implements EventCheckoutPostProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentProfileRepository $paymentProfileRepository,
    ) {
    }

    public function postProcess(
        ProcessPaymentRequestDTO $dto,
        EventCheckout $eventCheckout,
        Company $company,
        Employee $employee,
        ?AuthNetChargeResponseDTO $chargeResponse = null,
    ): void {
        if (!$chargeResponse) {
            return;
        }

        $this->storePaymentInfoOnCheckout($eventCheckout, $chargeResponse);
        $this->storePaymentProfileEntity($employee, $chargeResponse);
    }

    private function storePaymentInfoOnCheckout(
        EventCheckout $eventCheckout,
        AuthNetChargeResponseDTO $chargeResponse,
    ): void {
        $eventCheckout->setAuthnetCustomerProfileId($chargeResponse->customerProfileId);
        $eventCheckout->setAuthnetPaymentProfileId($chargeResponse->paymentProfileId);
        $eventCheckout->setCardLast4($chargeResponse->accountLast4);
        $eventCheckout->setCardType($chargeResponse->accountType);
    }

    private function storePaymentProfileEntity(
        Employee $employee,
        AuthNetChargeResponseDTO $chargeResponse,
    ): void {
        if (!$chargeResponse->customerProfileId || !$chargeResponse->paymentProfileId) {
            return;
        }

        $existing = $this->paymentProfileRepository->findOneByEmployeeAndAuthNetProfiles(
            $employee,
            $chargeResponse->customerProfileId,
            $chargeResponse->paymentProfileId,
        );

        if ($existing) {
            if ($chargeResponse->accountLast4 && $chargeResponse->accountLast4 !== $existing->getCardLast4()) {
                $existing->setCardLast4($chargeResponse->accountLast4);
            }

            if ($chargeResponse->accountType && $chargeResponse->accountType !== $existing->getCardType()) {
                $existing->setCardType($chargeResponse->accountType);
            }

            return;
        }

        $paymentProfile = new PaymentProfile();
        $paymentProfile->setEmployee($employee);
        $paymentProfile->setAuthnetCustomerId($chargeResponse->customerProfileId);
        $paymentProfile->setAuthnetPaymentProfileId($chargeResponse->paymentProfileId);
        $paymentProfile->setCardLast4($chargeResponse->accountLast4);
        $paymentProfile->setCardType($chargeResponse->accountType);

        $this->entityManager->persist($paymentProfile);
    }
}

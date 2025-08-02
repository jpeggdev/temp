<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service;

use App\DTO\AuthNet\AuthNetChargeRequestDTO;
use App\Entity\Company;
use App\Entity\Employee;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Request\ProcessPaymentRequestDTO;
use App\Module\EventRegistration\Feature\EventRegistration\DTO\Response\ProcessPaymentResponseDTO;
use App\Module\EventRegistration\Feature\EventRegistration\Exception\PaymentException;
use App\Module\EventRegistration\Feature\EventRegistration\Service\PostProcessor\EventCheckoutPostProcessingService;
use App\Module\EventRegistration\Feature\EventRegistration\Service\Validator\EventCheckoutValidationService;
use App\Repository\EventCheckoutRepository;
use App\Service\Authnet\AuthnetService;
use Doctrine\ORM\EntityManagerInterface;

final readonly class ProcessPaymentService
{
    public function __construct(
        private EventCheckoutRepository $eventCheckoutRepository,
        private EventCheckoutValidationService $validationService,
        private EventCheckoutPostProcessingService $postProcessingService,
        private AuthnetService $authnetService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function processPayment(
        ProcessPaymentRequestDTO $dto,
        Company $company,
        Employee $employee,
    ): ProcessPaymentResponseDTO {
        $eventCheckout = $this->eventCheckoutRepository->findOneByUuidOrFail($dto->eventCheckoutSessionUuid);
        $this->validationService->validate($dto, $eventCheckout, $company, $employee);

        $finalAmount = $dto->amount;
        $chargeResult = null;

        if ($eventCheckout->hasOnlyWaitlistAttendees()) {
            $chargeResult = $this->authnetService->storePaymentProfileOnly(
                $this->buildAuthNetChargeDTO($dto, $employee)
            );

            if (!$chargeResult->isResponseSuccess()) {
                $errorMsg = $chargeResult->responseData['error'] ?? 'Payment profile creation failed.';
                throw new PaymentException($errorMsg);
            }
        } else {
            if ($finalAmount > 0.0) {
                $chargeResult = $this->authnetService->charge(
                    $this->buildAuthNetChargeDTO($dto, $employee)
                );

                if (!$chargeResult->isResponseSuccess()) {
                    $errorMsg = $chargeResult->responseData['error'] ?? 'Payment failed. Please contact support.';
                    throw new PaymentException($errorMsg);
                }
            }
        }

        $conn = $this->entityManager->getConnection();
        $conn->beginTransaction();

        try {
            $this->postProcessingService->postProcess(
                $dto,
                $eventCheckout,
                $company,
                $employee,
                $chargeResult
            );

            $this->entityManager->flush();
            $conn->commit();
        } catch (\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }

        return new ProcessPaymentResponseDTO(
            $chargeResult->transactionId,
            true
        );
    }

    private function buildAuthNetChargeDTO(
        ProcessPaymentRequestDTO $dto,
        Employee $employee,
    ): AuthNetChargeRequestDTO {
        return new AuthNetChargeRequestDTO(
            dataDescriptor: $dto->dataDescriptor,
            dataValue: $dto->dataValue,
            amount: $dto->amount,
            invoiceNumber: $dto->invoiceNumber,
            shouldCreatePaymentProfile: $dto->shouldCreatePaymentProfile,
            customerEmail: $employee->getUser()->getEmail(),
            customerId: (string) $employee->getId()
        );
    }
}

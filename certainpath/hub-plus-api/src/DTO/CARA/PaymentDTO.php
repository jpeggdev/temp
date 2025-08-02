<?php

namespace App\DTO\CARA;

use App\Entity\Payment;

class PaymentDTO
{
    public string $transactionId;
    public string $name;
    public string $postalCode;
    public float $amount;
    public ?string $errorCode;
    public ?string $errorMessage;
    public string $customerProfileId;
    public string $paymentProfileId;
    public array $responseData;
    public string $cardType;
    public string $cardLast4;
    public string $uuid;
    public \DateTimeImmutable $createdAt;

    public function __construct(
        string $transactionId,
        string $name,
        string $postalCode,
        float $amount,
        string $customerProfileId,
        string $paymentProfileId,
        array $responseData,
        string $cardType,
        string $cardLast4,
        string|\DateTimeImmutable $createdAt,
        string $uuid,
        ?string $errorCode,
        ?string $errorMessage,
    ) {
        $this->transactionId = $transactionId;
        $this->name = $name;
        $this->postalCode = $postalCode;
        $this->amount = $amount;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->customerProfileId = $customerProfileId;
        $this->paymentProfileId = $paymentProfileId;
        $this->responseData = $responseData;
        $this->cardType = $cardType;
        $this->cardLast4 = $cardLast4;
        $this->uuid = $uuid;
        if (is_string($createdAt)) {
            try {
                $createdAt = new \DateTimeImmutable($createdAt);
            } catch (\Exception) {
                $createdAt = new \DateTimeImmutable();
            }
        }
        $this->createdAt = $createdAt;
    }

    public static function fromEntity(Payment $entity): self
    {
        // Extract name from Employee if available
        $name = '';
        if ($entity->getCreatedBy()) {
            $user = $entity->getCreatedBy()->getUser();
            if ($user) {
                $name = trim($user->getFirstName().' '.$user->getLastName());
            }
        }

        // Extract postal code from Employee's company if available
        $postalCode = '';
        if (
            $entity->getCreatedBy()
            && $entity->getCreatedBy()->getCompany()
        ) {
            $postalCode = $entity->getCreatedBy()->getCompany()->getZipCode() ?? '';
        }

        return new self(
            $entity->getTransactionId(),
            $name,
            $postalCode,
            (float) $entity->getAmount(),
            $entity->getCustomerProfileId() ?? '',
            $entity->getPaymentProfileId() ?? '',
            $entity->getResponseData() ?? [],
            $entity->getCardType() ?? '',
            $entity->getCardLast4() ?? '',
            $entity->getCreatedAt(),
            $entity->getUuid(),
            $entity->getErrorCode(),
            $entity->getErrorMessage()
        );
    }
}

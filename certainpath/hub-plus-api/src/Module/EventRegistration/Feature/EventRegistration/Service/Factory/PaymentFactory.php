<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Service\Factory;

use App\DTO\AuthNet\AuthNetChargeResponseDTO;
use App\Entity\Employee;
use App\Entity\Payment;

final class PaymentFactory
{
    public function createPayment(
        AuthNetChargeResponseDTO $chargeResponse,
        float $amount,
        Employee $employee,
    ): Payment {
        $payment = new Payment();
        $payment->setTransactionId($chargeResponse->transactionId ?? '');
        $payment->setAmount(number_format($amount, 2, '.', ''));
        $payment->setCardType($chargeResponse->accountType);
        $payment->setCardLast4($chargeResponse->accountLast4);
        $payment->setCustomerProfileId($chargeResponse->customerProfileId);
        $payment->setPaymentProfileId($chargeResponse->paymentProfileId);
        $payment->setResponseData($chargeResponse->responseData);
        $payment->setCreatedBy($employee);

        if (!$chargeResponse->isResponseSuccess()) {
            $payment->setErrorCode($chargeResponse->responseCode);
            $payment->setErrorMessage($chargeResponse->error);
        }

        return $payment;
    }
}

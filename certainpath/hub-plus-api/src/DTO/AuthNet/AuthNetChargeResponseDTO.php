<?php

declare(strict_types=1);

namespace App\DTO\AuthNet;

class AuthNetChargeResponseDTO
{
    public function __construct(
        public ?string $transactionId = null,
        public ?string $customerProfileId = null,
        public ?string $paymentProfileId = null,
        public ?string $responseCode = null,
        public ?string $error = null,
        public ?string $accountLast4 = null,
        public ?string $accountType = null,
        public array $responseData = [],
    ) {
    }

    public function isResponseSuccess(): bool
    {
        return '1' === $this->responseCode;
    }
}

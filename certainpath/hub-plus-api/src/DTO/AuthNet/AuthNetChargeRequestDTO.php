<?php

declare(strict_types=1);

namespace App\DTO\AuthNet;

use Symfony\Component\Validator\Constraints as Assert;

final class AuthNetChargeRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Data descriptor should not be blank.')]
        public string $dataDescriptor,
        #[Assert\NotBlank(message: 'Data value should not be blank.')]
        public string $dataValue,
        #[Assert\Positive(message: 'Amount must be positive.')]
        public float $amount,
        #[Assert\NotBlank(message: 'Invoice number is required.')]
        public string $invoiceNumber,
        #[Assert\Type('bool')]
        public bool $shouldCreatePaymentProfile = false,
        #[Assert\Email(message: 'Invalid email format.')]
        public ?string $customerEmail = null,
        public ?string $customerId = null,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class ProcessPaymentRequestDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Data descriptor should not be blank.')]
        public string $dataDescriptor,
        #[Assert\NotBlank(message: 'Data value should not be blank.')]
        public string $dataValue,
        #[Assert\GreaterThanOrEqual(
            value: 0,
            message: 'Amount must be zero or positive.'
        )]
        public float $amount,
        #[Assert\NotBlank(message: 'Invoice number is required.')]
        public string $invoiceNumber,
        #[Assert\NotBlank(message: 'Event checkout session UUID is required.')]
        public string $eventCheckoutSessionUuid,
        #[Assert\Type('bool')]
        public bool $shouldCreatePaymentProfile = false,
        #[Assert\GreaterThanOrEqual(
            value: 0,
            message: 'Voucher quantity must be zero or positive.'
        )]
        public ?int $voucherQuantity = 0,
        public ?string $discountCode = null,
        #[Assert\GreaterThanOrEqual(
            value: 0,
            message: 'Discount amount must be zero or positive.'
        )]
        public ?float $discountAmount = 0.0,
        #[Assert\Choice(
            choices: ['percentage', 'fixed_amount'],
            message: 'Invalid admin discount type.'
        )]
        public ?string $adminDiscountType = null,
        #[Assert\GreaterThanOrEqual(
            value: 0,
            message: 'Admin discount value must be zero or positive.'
        )]
        public ?float $adminDiscountValue = 0.0,
        public ?string $adminDiscountReason = null,
    ) {
    }
}

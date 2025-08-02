<?php

declare(strict_types=1);

namespace App\DTO\Response;

class StochasticCustomerResponseDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public ?bool $isNewCustomer,
        public ?bool $isRepeatCustomer,
        public ?bool $hasInstallation,
        public ?bool $hasSubscription,
        public ?int $countInvoices,
        public ?string $balanceTotal,
        public ?string $invoiceTotal,
        public ?string $lifetimeValue,
        public ?\DateTimeInterface $firstInvoicedAt,
        public ?\DateTimeInterface $lastInvoicedAt,
        public string $companyName,
        public ?StochasticAddressResponseDTO $address,
        public ?bool $doNotMail,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $address = null;
        if (isset($data['address']) && is_array($data['address'])) {
            $address = StochasticAddressResponseDTO::fromArray($data['address']);
        }

        return new self(
            $data['id'],
            $data['name'],
            $data['isNewCustomer'] ?? null,
            $data['isRepeatCustomer'] ?? null,
            $data['hasInstallation'] ?? null,
            $data['hasSubscription'] ?? null,
            $data['countInvoices'] ?? null,
            $data['balanceTotal'] ?? null,
            $data['invoiceTotal'] ?? null,
            $data['lifetimeValue'] ?? null,
            isset($data['firstInvoicedAt']) ? new \DateTimeImmutable($data['firstInvoicedAt']) : null,
            isset($data['lastInvoicedAt']) ? new \DateTimeImmutable($data['lastInvoicedAt']) : null,
            $data['companyName'],
            $address,
            $data['doNotMail'] ?? null
        );
    }
}

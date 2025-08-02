<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Customer;
use App\Entity\Prospect;
use App\Entity\Address;

class CustomerListResponseDTO
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
        public ?string $companyName,
        public ?\DateTimeInterface $createdAt,
        public ?\DateTimeInterface $updatedAt,
        public ?AddressResponseDTO $address,
        public ?string $firstInvoicedAt,
        public ?string $lastInvoicedAt,
        public ?bool $doNotMail,
    ) {
    }

    public static function fromEntity(Customer $customer): self
    {
        $prospect = $customer->getProspect();

        $preferredAddress = $prospect?->getPreferredAddress();
        if (!$preferredAddress && $prospect instanceof Prospect) {
            $preferredAddress = $prospect->getMostRecentValidAddress();
        }

        $addressDto = null;
        if ($preferredAddress instanceof Address) {
            $addressDto = AddressResponseDTO::fromEntity($preferredAddress);
        }

        return new self(
            $customer->getId(),
            $customer->getName(),
            $customer->isNewCustomer(),
            $customer->isRepeatCustomer(),
            $customer->hasInstallation(),
            $customer->hasSubscription(),
            $customer->getCountInvoices(),
            $customer->getBalanceTotal(),
            $customer->getInvoiceTotal(),
            $customer->getLifetimeValue(),
            $customer->getCompany()?->getName(),
            $customer->getCreatedAt(),
            $customer->getUpdatedAt(),
            $addressDto,
            $customer->getFirstInvoicedAt()?->format('Y-m-d'),
            $customer->getLastInvoicedAt()?->format('Y-m-d'),
            $prospect?->isDoNotMail(),
        );
    }
}

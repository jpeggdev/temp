<?php

namespace App\ValueObjects;

use DateTimeInterface;

class CustomerObject extends AbstractObject
{
    public ?string $name = null;
    public bool $hasInstallation = false;
    public bool $hasSubscription = false;
    public int $legacyCountInvoices = 0;
    public string $legacyLifetimeValue = '0.00';
    public string $legacyFirstSaleAmount = '0.00';
    public ?string $legacyLastInvoiceNumber = null;
    public ?DateTimeInterface $legacyFirstInvoicedAt = null;
    public ?ProspectObject $prospect = null;
    public ?int $prospectId = null;
    public ?AddressObject $address = null;
    public ?int $addressId = null;

    public function getTableName(): string
    {
        return 'customer';
    }

    public function getTableSequence(): string
    {
        return 'customer_id_seq';
    }

    public function isValid(): bool
    {
        return (
            !empty($this->companyId) &&
            !empty($this->name)
        );
    }

    public function toArray(): array
    {
        $createdAtFmt = $this->formatDate(
            $this->createdAt
        );

        $updatedAtFmt = $this->formatDate(
            $this->updatedAt
        );

        return [
            'id' => $this->_id,
            'company_id' => $this->companyId,
            'is_active' => $this->isActive,
            'is_deleted' => $this->isDeleted,
            'name' => $this->name,
            'has_installation' => $this->hasInstallation,
            'has_subscription' => $this->hasSubscription,
            'legacy_count_invoices' => $this->legacyCountInvoices,
            'legacy_lifetime_value' => $this->legacyLifetimeValue,
            'legacy_first_sale_amount' => $this->legacyFirstSaleAmount,
            'legacy_last_invoice_number' => $this->legacyLastInvoiceNumber,
            'legacy_first_invoiced_at' => $this->formatDate($this->legacyFirstInvoicedAt),
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function isCustomer(): bool
    {
        return (
            !empty($this->legacyFirstInvoicedAt) ||
            !empty((float) $this->legacyLifetimeValue) ||
            !empty((float) $this->legacyFirstSaleAmount) ||
            !empty($this->legacyLastInvoiceNumber) ||
            $this->hasInstallation ||
            $this->hasSubscription
        );
    }

    public function populate(): static
    {
        return $this;
    }
}

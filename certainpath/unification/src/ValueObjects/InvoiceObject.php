<?php

namespace App\ValueObjects;

use DateTimeInterface;

class InvoiceObject extends AbstractObject
{
    protected const KEY_FIELDS = [
        'description',
        'invoiceNumber',
        'invoicedAt',
        'prospect',
        'total',
    ];

    public float $total = 0.00;
    public float $subTotal = 0.00;
    public float $tax = 0.00;
    public float $balance = 0.00;
    public ?string $description = null;
    public ?string $invoiceNumber = null;
    public ?string $revenueType = null;
    public ?string $externalId = null;
    public ?DateTimeInterface $invoicedAt = null;
    public ?CustomerObject $customer = null;
    public ?int $customerId = null;
    public ?ProspectObject $prospect = null;
    public ?int $prospectId = null;
    public ?AddressObject $address = null;
    public ?string $zone = null;
    public ?string $jobType = null;
    public ?string $summary = null;

    public function getTableName(): string
    {
        return 'invoice';
    }

    public function getTableSequence(): string
    {
        return 'invoice_id_seq';
    }

    public function isValid(): bool
    {
        return (
            !empty($this->invoicedAt) &&
            !empty($this->description) &&
            !empty($this->customerId)
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

        $invoicedAtFmt = $this->formatDate(
            $this->invoicedAt
        );

        return [
            'id' => $this->_id,
            'company_id' => $this->companyId,
            'trade_id' => $this->tradeId,
            'customer_id' => $this->customerId,
            'external_id' => $this->externalId,
            'total' => $this->total,
            'sub_total' => $this->subTotal,
            'tax' => $this->tax,
            'balance' => $this->balance,
            'invoice_number' => $this->invoiceNumber,
            'revenue_type' => $this->revenueType,
            'zone' => $this->zone,
            'job_type' => $this->jobType,
            'summary' => $this->summary,
            'description' => $this->description,
            'invoiced_at' => $invoicedAtFmt,
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function populate(): static
    {
        $invoicedAtFmt = null;
        if ($this->invoicedAt instanceof DateTimeInterface) {
            $invoicedAtFmt = $this->invoicedAt->format('Y-m-d');
        }

        $this->key = self::createKey([
            'prospect' => $this->prospect?->getKey(),
            'invoiceNumber' => $this->invoiceNumber,
            'total' => number_format($this->total, 2, '.', null),
            'description' => $this->getHashedString($this->description),
            'invoicedAt' => $invoicedAtFmt,
        ]);

        return $this;
    }
}

<?php

namespace App\ValueObjects;

use DateTimeInterface;

class SubscriptionObject extends AbstractObject
{
    public ?string $name = null;
    public ?DateTimeInterface $startsAt = null;
    public ?DateTimeInterface $endsAt = null;
    public ?string $frequency = null;
    public ?string $price = null;
    public ?CustomerObject $customer = null;
    public ?int $customerId = null;
    public ?ProspectObject $prospect = null;
    public ?int $prospectId = null;

    protected const KEY_FIELDS = [
        'name',
        'frequency',
        'price'
    ];

    public function getTableName(): string
    {
        return 'subscription';
    }

    public function getTableSequence(): string
    {
        return 'subscription_id_seq';
    }

    public function isValid(): bool
    {
        return (
            !empty($this->companyId) &&
            !empty($this->customerId) &&
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
            'customer_id' => $this->customerId,
            'name' => $this->name,
            'is_active' => $this->isActive,
            'is_deleted' => $this->isDeleted,
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function populate(): static
    {
        $this->key = self::createKey([
            'name' => $this->name,
            'frequency' => $this->frequency,
            'price' => $this->price,
        ]);

        return $this;
    }
}

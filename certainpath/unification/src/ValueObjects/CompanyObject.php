<?php

namespace App\ValueObjects;

use App\Entity\Company;
use DateTimeInterface;

class CompanyObject extends AbstractObject
{
    public ?string $identifier = null;
    public ?string $name = null;
    public ?DateTimeInterface $createdAt = null;
    public ?DateTimeInterface $updatedAt = null;

    protected const KEY_FIELDS = [ ];

    public function getTableName(): string
    {
        return 'company';
    }

    public function getTableSequence(): string
    {
        return 'company_id_seq';
    }

    public function isValid(): bool
    {
        return (
            !empty($this->identifier) &&
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
            'identifier' => $this->identifier,
            'name' => $this->name,
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function populate(): static
    {
        return $this;
    }

    public static function fromEntity(Company $company): static
    {
        return new static([
            '_id' => (int)$company->getId(),
            'identifier' => $company->getIdentifier(),
            'name' => $company->getName(),
            'isActive' => $company->isActive(),
            'updatedAt' => $company->getUpdatedAt(),
            'createdAt' => $company->getCreatedAt(),
        ]);
    }
}

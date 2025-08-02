<?php

namespace App\ValueObjects;

use function App\Functions\app_getPostalCodeShort;

class ProspectObject extends AbstractObject
{
    public ?string $externalId = null;
    public ?string $fullName = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $state = null;
    public ?string $postalCode = null;
    public ?string $postalCodeShort = null;
    public bool $doNotContact = false;
    public bool $doNotMail = false;
    public ?CustomerObject $customer = null;
    public ?int $customerId = null;
    public ?AddressObject $address = null;
    public ?int $preferredAddressId = null;
    public ?int $addressId = null;
    public bool $isActiveMembership = false;
    public ?string $version = null;

    protected const KEY_FIELDS = [
        'fullName',
        'address1',
        'address2',
        'city',
        'state',
        'postalCode',
    ];

    public function __construct(array $record = [])
    {
        parent::__construct($record);
    }

    public function getTableName(): string
    {
        return 'prospect';
    }

    public function getTableSequence(): string
    {
        return 'prospect_id_seq';
    }

    public function isValid(): bool
    {
        return (
            !empty($this->externalId) &&
            !empty($this->companyId) &&
            !empty($this->fullName) &&
            !empty($this->address1) &&
            !empty($this->city) &&
            !empty($this->state) &&
            !empty($this->postalCode)
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
            'external_id' => $this->externalId,
            'company_id' => $this->companyId,
            'preferred_address_id' => $this->preferredAddressId,
            'is_active' => $this->isActive,
            'is_deleted' => $this->isDeleted,
            'full_name' => $this->fullName,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postalCode,
            'postal_code_short' => $this->postalCodeShort,
            'do_not_contact' => $this->doNotContact,
            'do_not_mail' => $this->doNotMail,
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function populate(): static
    {
        if ($this->postalCode) {
            $this->postalCodeShort = app_getPostalCodeShort($this->postalCode);
        }
        $this->key = self::createKey([
            'fullName' => $this->fullName,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state' => $this->state,
            'postalCode' => $this->postalCodeShort,
        ]);

        return $this;
    }
}

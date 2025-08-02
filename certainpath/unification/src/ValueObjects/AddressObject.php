<?php

namespace App\ValueObjects;

use App\Entity\AbstractAddress;
use DateTimeInterface;

use function App\Functions\app_getPostalCodeShort;
use function App\Functions\app_upper;

class AddressObject extends AbstractObject
{
    public ?string $externalId = null;
    public string $type = 'RESIDENTIAL';
    public ?string $name = null;
    public ?string $address1 = null;
    public ?string $address2 = null;
    public ?string $city = null;
    public ?string $cityAbbreviation = null;
    public ?string $stateCode = null;
    public ?string $province = null;
    public ?string $country = null;
    public ?string $countryISOCode = null;

    public ?string $uspsStreetAddressAbbreviation = null;
    public ?string $uspsUrbanization = null;
    public ?string $uspsDeliveryPoint = null;
    public ?string $uspsCarrierRoute = null;
    public ?string $uspsDpvConfirmation = null;
    public ?string $uspsDpvCmra = null;
    public ?string $uspsCentralDeliveryPoint = null;
    public ?string $uspsBusiness = null;
    public ?string $uspsVacant = null;

    public bool $isVacant = false;
    public bool $isBusiness = false;
    public bool $isVerified = false;

    public ?string $age = null;
    public ?string $yearBuilt = null;
    public ?DateTimeInterface $verifiedAt = null;
    public ?string $postalCode = null;
    public ?string $postalCodeShort = null;

    public ?CustomerObject $customer = null;
    public ?int $customerId = null;
    public ?ProspectObject $prospect = null;
    public ?int $prospectId = null;

    private const ADDRESS_TYPES = [
        'COMMERCIAL',
        'RESIDENTIAL'
    ];
    protected const KEY_FIELDS = [
        'address1',
        'address2',
        'city',
        'stateCode',
        'postalCode',
    ];

    public function getTableName(): string
    {
        return 'address';
    }

    public function getTableSequence(): string
    {
        return 'address_id_seq';
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function isValid(): bool
    {
        return (
            !empty($this->externalId) &&
            !empty($this->companyId) &&
            !empty($this->address1) &&
            !empty($this->city) &&
            !empty($this->stateCode) &&
            !empty($this->postalCode) &&
            !empty($this->postalCodeShort)
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

        $verifiedAtFmt = $this->formatDate(
            $this->verifiedAt
        );

        return [
            'id' => $this->_id,
            'external_id' => $this->externalId,
            'company_id' => $this->companyId,
            'is_business' => $this->isBusiness,
            'is_vacant' => $this->isVacant,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'state_code' => $this->stateCode,
            'country_code' => $this->countryISOCode,
            'postal_code' => $this->postalCode,
            'verified_at' => $verifiedAtFmt,
            'created_at' => $createdAtFmt,
            'updated_at' => $updatedAtFmt,
        ];
    }

    public function setType(string $type): static
    {
        if (in_array($type, self::ADDRESS_TYPES)) {
            $this->type = $type;
        }

        return $this;
    }

    public function populate(): static
    {
        if ($this->postalCode) {
            $this->postalCodeShort = app_getPostalCodeShort($this->postalCode);
        }

        $this->key = self::createKey([
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'stateCode' => $this->stateCode,
            'postalCode' => $this->postalCodeShort,
        ]);

        $this->computeAddressType();
        $this->computeIsVacant();
        $this->computeIsVerified();

        return $this;
    }

    private function computeIsVacant(): void
    {
        $this->isVacant = false;
        if (app_upper($this->uspsVacant) === 'Y') {
            $this->isVacant = true;
        }
    }

    private function computeIsVerified(): void
    {
        if (!$this->isVerified) {
            $this->isVerified = (!empty($this->verifiedAt));
        }
    }

    private function computeAddressType(): void
    {
        $this->isBusiness = false;
        if (app_upper($this->uspsDpvCmra) === 'Y') {
            $this->isBusiness = true;
        }

        if (app_upper($this->uspsBusiness) === 'Y') {
            $this->isBusiness = true;
        }

        if (app_upper($this->uspsCentralDeliveryPoint) === 'Y') {
            $this->isBusiness = true;
        }

        if ($this->isBusiness) {
            $this->setType('COMMERCIAL');
        }
    }

    public function getPostalCodeShort(): ?string
    {
        return $this->postalCodeShort;
    }

    public static function fromEntity(AbstractAddress $address): static
    {
        return new static([
            '_id' => (int)$address->getId(),
            'address1' => $address->getAddress1(),
            'address2' => $address->getAddress2(),
            'city' => $address->getCity(),
            'stateCode' => $address->getStateCode(),
            'postalCode' => $address->getPostalCode(),
            'countryISOCode' => $address->getCountryCode(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Domain;

use App\Entity\RestrictedAddress;
use DateTimeInterface;

class RestrictedAddressDTO
{
    public function __construct(
        public int $id,
        public string $address1,
        public ?string $address2,
        public string $city,
        public ?string $stateCode,
        public ?string $postalCode,
        public ?string $countryCode,
        public bool $isBusiness,
        public bool $isVacant,
        public bool $isVerified,
        public DateTimeInterface $createdAt,
        public DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(RestrictedAddress $restrictedAddress): static
    {
        return new static(
            $restrictedAddress->getId(),
            $restrictedAddress->getAddress1(),
            $restrictedAddress->getAddress2(),
            $restrictedAddress->getCity(),
            $restrictedAddress->getStateCode(),
            $restrictedAddress->getPostalCode(),
            $restrictedAddress->getCountryCode(),
            $restrictedAddress->isBusiness(),
            $restrictedAddress->isVacant(),
            $restrictedAddress->isVerified(),
            $restrictedAddress->getCreatedAt(),
            $restrictedAddress->getUpdatedAt(),
        );
    }
}

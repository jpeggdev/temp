<?php

declare(strict_types=1);

namespace App\DTO\Domain;

use App\Entity\Address;
use DateTimeInterface;

class AddressDTO
{
    public function __construct(
        public int $id,
        public string $address1,
        public ?string $address2,
        public string $city,
        public string $stateCode,
        public string $postalCode,
        public string $countryCode,
        public bool $isBusiness,
        public bool $isVacant,
        public bool $isVerified,
        public bool $isDoNotMail,
        public bool $isGlobalDoNotMail,
        public DateTimeInterface $createdAt,
        public DateTimeInterface $updatedAt,
    ) {
    }

    public static function fromEntity(Address $address): static
    {
        return new static(
            $address->getId(),
            $address->getAddress1(),
            $address->getAddress2(),
            $address->getCity(),
            $address->getStateCode(),
            $address->getPostalCode(),
            $address->getCountryCode(),
            $address->isBusiness(),
            $address->isVacant(),
            $address->isVerified(),
            $address->isDoNotMail(),
            $address->isGlobalDoNotMail(),
            $address->getCreatedAt(),
            $address->getUpdatedAt(),
        );
    }
}

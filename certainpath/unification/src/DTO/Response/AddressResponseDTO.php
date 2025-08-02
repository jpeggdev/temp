<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Address;

readonly class AddressResponseDTO
{
    public function __construct(
        public int $id,
        public ?string $address1,
        public ?string $address2,
        public ?string $city,
        public ?string $stateCode,
        public ?string $postalCode,
        public ?string $countryCode,
        public ?bool $isBusiness,
        public ?bool $isVacant,
        public ?bool $isDoNotMail,
        public ?bool $isGlobalDoNotMail
    ) {
    }

    public static function fromEntity(Address $address): self
    {
        return new self(
            id: $address->getId(),
            address1: $address->getAddress1(),
            address2: $address->getAddress2(),
            city: $address->getCity(),
            stateCode: $address->getStateCode(),
            postalCode: $address->getPostalCode(),
            countryCode: $address->getCountryCode(),
            isBusiness: $address->isBusiness(),
            isVacant: $address->isVacant(),
            isDoNotMail: $address->isDoNotMail(),
            isGlobalDoNotMail: $address->isGlobalDoNotMail()
        );
    }
}

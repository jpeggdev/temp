<?php

declare(strict_types=1);

namespace App\DTO\Response;

use App\Entity\Address;

readonly class AddressesMatchesResponseDTO
{
    public function __construct(
        public ?string $address1,
        public ?string $address2,
        public ?string $city,
        public ?string $state,
        public ?string $zip,
        public ?string $externalId,
        public bool $isMatched,
    ) {
    }

    public static function fromEntity(
        Address $address,
        bool $isMatched
    ): self {
        return new self(
            address1: $address->getAddress1(),
            address2: $address->getAddress2(),
            city: $address->getCity(),
            state: $address->getStateCode(),
            zip: $address->getPostalCode(),
            externalId: $address->getExternalId(),
            isMatched: $isMatched,
        );
    }
}

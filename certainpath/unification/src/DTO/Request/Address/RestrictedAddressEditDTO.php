<?php

namespace App\DTO\Request\Address;

use App\Entity\RestrictedAddress;
use Symfony\Component\Validator\Constraints as Assert;

class RestrictedAddressEditDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public ?string $address1 = null,
        #[Assert\Length(max: 255)]
        public ?string $address2 = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public ?string $city = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 2)]
        public ?string $stateCode = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 255)]
        public ?string $postalCode = null,
        #[Assert\NotBlank]
        #[Assert\Length(max: 2)]
        public ?string $countryCode = null,
    ) {
    }

    public static function fromEntity(RestrictedAddress $restrictedAddress): static
    {
        return new static(
            $restrictedAddress->getAddress1(),
            $restrictedAddress->getAddress2(),
            $restrictedAddress->getCity(),
            $restrictedAddress->getStateCode(),
            $restrictedAddress->getPostalCode(),
            $restrictedAddress->getCountryCode(),
        );
    }
}

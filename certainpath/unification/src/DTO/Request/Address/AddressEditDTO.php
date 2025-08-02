<?php

namespace App\DTO\Request\Address;

use App\Entity\Address;
use Symfony\Component\Validator\Constraints as Assert;

class AddressEditDTO
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
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['true', 'false'], message: 'isDoNotMail must be true or false')]
        public ?string $isDoNotMail = 'false',
    ) {
    }

    public static function fromEntity(Address $address): static
    {
        return new static(
            $address->getAddress1(),
            $address->getAddress2(),
            $address->getCity(),
            $address->getStateCode(),
            $address->getPostalCode(),
            $address->getCountryCode(),
            $address->isDoNotMail() ? 'true' : 'false',
        );
    }
}

<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class RestrictedAddressCreateUpdateDTO
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
}

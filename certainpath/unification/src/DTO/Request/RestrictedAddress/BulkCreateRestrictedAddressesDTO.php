<?php

namespace App\DTO\Request\RestrictedAddress;

use Symfony\Component\Validator\Constraints as Assert;

class BulkCreateRestrictedAddressesDTO
{
    public function __construct(
        #[Assert\Type('array')]
        public array $addresses,
    ) {
    }
}
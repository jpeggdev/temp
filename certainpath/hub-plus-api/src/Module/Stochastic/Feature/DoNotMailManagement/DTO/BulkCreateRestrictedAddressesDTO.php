<?php

namespace App\Module\Stochastic\Feature\DoNotMailManagement\DTO;

class BulkCreateRestrictedAddressesDTO
{
    public function __construct(
        public array $addresses = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\DTO\Request\Customer;

class UpdateStochasticCustomerDoNotMailRequestDTO
{
    public function __construct(
        public bool $doNotMail,
    ) {
    }
}

<?php

namespace App\DTO\Request\Customer;

class UpdateCustomerDoNotMailDTO
{
    public function __construct(
        public ?bool $doNotMail = null,
        public ?int $customerId = null, // Kept for backward compatibility, not used
    ) {}
}
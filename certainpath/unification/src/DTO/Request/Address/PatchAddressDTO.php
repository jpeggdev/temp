<?php

namespace App\DTO\Request\Address;

class PatchAddressDTO
{
    public function __construct(
        public ?bool $doNotMail = null,
        public ?int $addressId = null, // Kept for backward compatibility, not used
    ) {}
}
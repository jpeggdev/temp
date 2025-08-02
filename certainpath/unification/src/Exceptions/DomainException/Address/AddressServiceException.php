<?php

namespace App\Exceptions\DomainException\Address;

use App\Exceptions\AppException;

class AddressServiceException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'AddressService error.';
    }
}
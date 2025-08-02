<?php

namespace App\Exceptions\DomainException\RestrictedAddress;

use App\Exceptions\AppException;

class RestrictedAddressAlreadyExistsException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Restricted Address already exists';
    }
}

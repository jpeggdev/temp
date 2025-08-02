<?php

namespace App\Exceptions\DomainException\Address;

use App\Exceptions\AppException;

class AddressAlreadyExistsException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Address already exists';
    }
}

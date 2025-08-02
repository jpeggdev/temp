<?php

namespace App\Exceptions;

class AddressIsInvalid extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Address is invalid: ';
    }
}

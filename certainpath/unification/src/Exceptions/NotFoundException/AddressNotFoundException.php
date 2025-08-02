<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class AddressNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Address was not found.';
    }
}

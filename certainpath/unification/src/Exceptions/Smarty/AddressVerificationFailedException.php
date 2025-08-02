<?php

namespace App\Exceptions\Smarty;

use App\Exceptions\AppException;

class AddressVerificationFailedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Smarty API: The address could not be verified, as it may be undeliverable or has certain issues.';
    }
}

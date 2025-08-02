<?php

namespace App\Exceptions\USPS;

use App\Exceptions\AppException;

class USPSAddressVerificationValidationException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'USPS Address Validation Failed';
    }
}

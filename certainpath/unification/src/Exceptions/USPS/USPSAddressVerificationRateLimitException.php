<?php

namespace App\Exceptions\USPS;

use App\Exceptions\AppException;

class USPSAddressVerificationRateLimitException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'USPS Rate Limit Exceeded';
    }
}

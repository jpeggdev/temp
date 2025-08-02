<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CompanyProspectsNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'No prospects found for the Company.';
    }
}

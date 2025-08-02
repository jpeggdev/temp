<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CompanyNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Company was not found.';
    }
}

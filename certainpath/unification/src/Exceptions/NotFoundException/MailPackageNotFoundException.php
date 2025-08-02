<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class MailPackageNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Mail Package was not found.';
    }
}

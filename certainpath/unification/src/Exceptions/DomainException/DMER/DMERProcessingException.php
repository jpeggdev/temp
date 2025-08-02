<?php

namespace App\Exceptions\DomainException\DMER;

use App\Exceptions\AppException;

class DMERProcessingException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'DMER Processing Error.';
    }
}

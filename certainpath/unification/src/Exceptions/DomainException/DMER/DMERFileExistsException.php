<?php

namespace App\Exceptions\DomainException\DMER;

use App\Exceptions\AppException;

class DMERFileExistsException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'File already exists.';
    }
}

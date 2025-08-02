<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class BatchStatusNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Batch Status was not found.';
    }
}
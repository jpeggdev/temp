<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class BatchNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Batch was not found.';
    }
}

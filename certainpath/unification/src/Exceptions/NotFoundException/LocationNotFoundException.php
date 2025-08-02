<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class LocationNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Location was not found.';
    }
}

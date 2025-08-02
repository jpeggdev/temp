<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class ProspectFilterRuleNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Prospect filter rule was not found';
    }
}

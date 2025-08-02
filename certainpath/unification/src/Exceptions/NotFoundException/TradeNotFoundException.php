<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class TradeNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Trade was not found.';
    }
}

<?php

namespace App\Exception;

class UnsupportedTrade extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Unsupported trade: ';
    }
}

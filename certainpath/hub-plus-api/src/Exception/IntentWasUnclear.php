<?php

namespace App\Exception;

class IntentWasUnclear extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Intent was unclear. ';
    }
}

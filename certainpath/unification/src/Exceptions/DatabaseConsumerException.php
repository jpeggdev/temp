<?php

namespace App\Exceptions;

class DatabaseConsumerException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Error consuming remote source.';
    }
}
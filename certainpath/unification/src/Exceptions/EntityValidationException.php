<?php

namespace App\Exceptions;

class EntityValidationException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Invalid entity';
    }
}
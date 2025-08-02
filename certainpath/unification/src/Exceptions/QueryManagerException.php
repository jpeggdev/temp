<?php

namespace App\Exceptions;

class QueryManagerException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Query Manager Exception';
    }
}

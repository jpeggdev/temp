<?php

namespace App\Exceptions;

class FileConverterException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'File could not be converted.';
    }
}

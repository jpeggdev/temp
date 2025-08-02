<?php

namespace App\Exception;

class UnsupportedFileTypeException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Unsupported file type. ';
    }
}

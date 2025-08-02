<?php

namespace App\Exception;

class UnsupportedImportTypeException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Unsupported importType: ';
    }
}

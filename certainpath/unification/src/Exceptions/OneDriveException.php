<?php

namespace App\Exceptions;

class OneDriveException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'OneDrive error.';
    }
}

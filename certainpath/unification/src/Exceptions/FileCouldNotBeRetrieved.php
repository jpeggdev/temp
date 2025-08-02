<?php

namespace App\Exceptions;

class FileCouldNotBeRetrieved extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'File could not be retrieved.';
    }
}

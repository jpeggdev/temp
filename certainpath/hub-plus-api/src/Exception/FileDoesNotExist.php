<?php

namespace App\Exception;

class FileDoesNotExist extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'File was not found or could not be read. ';
    }
}

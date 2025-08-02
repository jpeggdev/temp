<?php

namespace App\Exception;

class NoFilePathWasProvided extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'No file path was provided.';
    }
}

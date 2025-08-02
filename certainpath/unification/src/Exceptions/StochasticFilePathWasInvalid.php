<?php

namespace App\Exceptions;

class StochasticFilePathWasInvalid extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Invalid File Path: ';
    }
}

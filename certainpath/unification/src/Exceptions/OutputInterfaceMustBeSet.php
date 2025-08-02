<?php

namespace App\Exceptions;

class OutputInterfaceMustBeSet extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'OutputInterface must be set';
    }
}

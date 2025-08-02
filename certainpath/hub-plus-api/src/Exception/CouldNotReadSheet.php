<?php

namespace App\Exception;

class CouldNotReadSheet extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Could not read the spreadsheet. ';
    }
}

<?php

namespace App\Exception;

class ReportDoesNotExist extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'This report does not exist.';
    }
}

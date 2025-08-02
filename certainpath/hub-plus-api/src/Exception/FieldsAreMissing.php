<?php

namespace App\Exception;

class FieldsAreMissing extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Required fields are missing: ';
    }
}

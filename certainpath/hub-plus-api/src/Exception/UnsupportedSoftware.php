<?php

namespace App\Exception;

class UnsupportedSoftware extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Unsupported software: ';
    }
}

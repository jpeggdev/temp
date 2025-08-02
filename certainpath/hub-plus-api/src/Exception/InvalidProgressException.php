<?php

declare(strict_types=1);

namespace App\Exception;

class InvalidProgressException extends AppException
{
    public function getDefaultMessage(): string
    {
        return 'Invalid progress value';
    }
}

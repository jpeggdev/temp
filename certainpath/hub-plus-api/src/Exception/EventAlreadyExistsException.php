<?php

declare(strict_types=1);

namespace App\Exception;

class EventAlreadyExistsException extends AppException
{
    public function getDefaultMessage(): string
    {
        return 'Event already exists';
    }
}

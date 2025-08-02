<?php

namespace App\Exceptions\InvalidArgumentException;

use InvalidArgumentException;

class InvalidDateFormatException extends InvalidArgumentException
{
    public function __construct(?string $date, string $message = null)
    {
        $messageTemplate = 'Invalid date format: %s. %s';
        parent::__construct(sprintf($messageTemplate, $date, $message));
    }
}

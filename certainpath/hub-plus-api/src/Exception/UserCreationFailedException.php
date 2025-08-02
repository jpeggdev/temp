<?php

declare(strict_types=1);

namespace App\Exception;

class UserCreationFailedException extends \RuntimeException
{
    public function __construct(string $message = 'User creation failed', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

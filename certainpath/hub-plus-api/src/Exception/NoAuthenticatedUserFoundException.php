<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class NoAuthenticatedUserFoundException extends AuthenticationException
{
    public function __construct(string $message = 'No authenticated user found.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getMessageKey(): string
    {
        return 'No authenticated user found.';
    }
}

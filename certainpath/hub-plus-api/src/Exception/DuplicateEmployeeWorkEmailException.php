<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DuplicateEmployeeWorkEmailException extends ConflictHttpException
{
    public function __construct(string $message = 'This work email is already in use by another employee', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

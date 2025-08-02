<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EmployeeAlreadyExistsException extends ConflictHttpException
{
    public function __construct(string $message = 'An employee record already exists for this user and company.', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

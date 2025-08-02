<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EmployeeSessionEnrollmentAlreadyExistsException extends ConflictHttpException
{
    public function __construct(string $message = 'Employee is already enrolled in this session', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

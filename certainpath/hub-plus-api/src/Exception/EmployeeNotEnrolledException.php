<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EmployeeNotEnrolledException extends NotFoundHttpException
{
    public function __construct(string $message = 'Employee is not enrolled in this event', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

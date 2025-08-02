<?php

declare(strict_types=1);

namespace App\Exception;

class EmployeeNotFoundException extends \InvalidArgumentException
{
    public function __construct(int $employeeId, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Employee with ID %d not found', $employeeId), 404, $previous);
    }
}

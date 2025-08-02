<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventWaitlist\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class EmployeeNotFoundException extends NotFoundHttpException
{
    public static function forEmployeeId(int $employeeId): self
    {
        return new self(
            sprintf('Employee with ID %d not found.', $employeeId)
        );
    }
}

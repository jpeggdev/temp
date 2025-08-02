<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class EmployeeAlreadyEnrolledException extends \RuntimeException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Employee with email %s is already enrolled in this session.', $email));
    }
}

<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToLifetimeValueDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get lifetime value data.";
    }
}

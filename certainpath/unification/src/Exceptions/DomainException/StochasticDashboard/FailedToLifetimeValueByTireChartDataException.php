<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToLifetimeValueByTireChartDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get lifetime value chart data.";
    }
}

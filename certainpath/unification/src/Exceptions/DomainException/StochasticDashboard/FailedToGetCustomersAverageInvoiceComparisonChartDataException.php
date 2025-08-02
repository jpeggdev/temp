<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToGetCustomersAverageInvoiceComparisonChartDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get customers average invoice comparison chart data.";
    }
}

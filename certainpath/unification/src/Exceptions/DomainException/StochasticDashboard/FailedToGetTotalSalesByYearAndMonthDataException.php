<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToGetTotalSalesByYearAndMonthDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get Total Sales by Year and Month data.";
    }
}

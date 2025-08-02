<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToGetTotalSalesByZipCodeDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get Total Sales by Zip Code data.";
    }
}

<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToGetPercentageOfNewCustomersByZipCodeDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get percentage of new customers by zip code data.";
    }
}

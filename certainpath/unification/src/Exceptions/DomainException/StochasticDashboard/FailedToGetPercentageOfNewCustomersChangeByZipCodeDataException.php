<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToGetPercentageOfNewCustomersChangeByZipCodeDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get percentage of new customers by zip code table data.";
    }
}

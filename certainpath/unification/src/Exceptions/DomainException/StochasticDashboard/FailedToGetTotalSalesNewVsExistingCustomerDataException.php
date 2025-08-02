<?php

namespace App\Exceptions\DomainException\StochasticDashboard;

use App\Exceptions\AppException;

class FailedToGetTotalSalesNewVsExistingCustomerDataException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get total sales new vs. existing customer data.";
    }
}

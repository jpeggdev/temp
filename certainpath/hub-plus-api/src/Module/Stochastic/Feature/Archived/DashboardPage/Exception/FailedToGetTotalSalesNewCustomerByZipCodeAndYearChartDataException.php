<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetTotalSalesNewCustomerByZipCodeAndYearChartDataException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get the total sales new customer by zip code and year chart data';
        parent::__construct($message);
    }
}

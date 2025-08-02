<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetTotalSalesByZipCodeChartDataException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get the total sales by zip code chart data';
        parent::__construct($message);
    }
}

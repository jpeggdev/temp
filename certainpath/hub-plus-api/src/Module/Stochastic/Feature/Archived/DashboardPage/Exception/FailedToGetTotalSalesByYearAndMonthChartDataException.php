<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetTotalSalesByYearAndMonthChartDataException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get the total sales by year and month chart data';
        parent::__construct($message);
    }
}

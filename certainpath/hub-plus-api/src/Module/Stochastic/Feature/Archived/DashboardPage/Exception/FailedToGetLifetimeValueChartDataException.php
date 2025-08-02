<?php

namespace App\Module\Stochastic\Feature\Archived\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetLifetimeValueChartDataException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get the lifetime value chart data';
        parent::__construct($message);
    }
}

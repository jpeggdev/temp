<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetStochasticDashboardDataException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get stochastic dashboard charts data';
        parent::__construct($message);
    }
}

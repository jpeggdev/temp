<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetTradeFilterOptionsException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get trade filter options';
        parent::__construct($message);
    }
}

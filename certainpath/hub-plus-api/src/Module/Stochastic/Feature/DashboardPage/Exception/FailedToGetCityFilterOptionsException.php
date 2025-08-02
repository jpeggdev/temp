<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetCityFilterOptionsException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get city filter options';
        parent::__construct($message);
    }
}

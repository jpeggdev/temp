<?php

namespace App\Module\Stochastic\Feature\DashboardPage\Exception;

use App\Exception\UnificationAPIException;

class FailedToGetYearFilterOptionsException extends UnificationAPIException
{
    public function __construct()
    {
        $message = 'Failed to get year filter options';
        parent::__construct($message);
    }
}

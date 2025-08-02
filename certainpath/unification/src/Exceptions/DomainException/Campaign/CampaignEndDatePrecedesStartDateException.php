<?php

namespace App\Exceptions\DomainException\Campaign;

use InvalidArgumentException;

class CampaignEndDatePrecedesStartDateException extends InvalidArgumentException
{
    public function __construct(?string $startDate, ?string $endDate)
    {
        $message = 'The campaign end date cannot precede the start date. Start date: %s, End date: %s';
        parent::__construct(sprintf($message, $startDate, $endDate));
    }
}

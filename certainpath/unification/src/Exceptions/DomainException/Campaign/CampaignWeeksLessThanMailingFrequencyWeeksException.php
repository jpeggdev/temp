<?php

namespace App\Exceptions\DomainException\Campaign;

use InvalidArgumentException;

class CampaignWeeksLessThanMailingFrequencyWeeksException extends InvalidArgumentException
{
    public function __construct(int $campaignWeeks, int $mailingFrequencyWeeks)
    {
        $message = 'The campaign duration of %s weeks is too short for the selected mailing frequency of %s weeks.';
        parent::__construct(sprintf($message, $campaignWeeks, $mailingFrequencyWeeks));
    }
}

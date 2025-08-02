<?php

namespace App\Exceptions\InvalidArgumentException;

use InvalidArgumentException;

class InvalidCampaignPausedDate extends InvalidArgumentException
{
    public function __construct(string $campaignPausedDate)
    {
        $template = 'The date campaign was paused "%s" cannot be in the future';
        $message = sprintf($template, $campaignPausedDate);

        parent::__construct($message);
    }
}

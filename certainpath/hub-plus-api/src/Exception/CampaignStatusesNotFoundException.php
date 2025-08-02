<?php

namespace App\Exception;

class CampaignStatusesNotFoundException extends UnificationAPIException
{
    public function __construct(string $message = 'No campaign statuses found.', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

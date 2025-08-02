<?php

namespace App\Exception\NotFoundException;

use App\Exception\UnificationAPIException;

class CampaignNotFoundException extends UnificationAPIException
{
    public function __construct(int $campaignId, ?\Exception $previous = null)
    {
        $message = "Campaign with ID {$campaignId} not found.";
        parent::__construct($message, 0, $previous);
    }
}

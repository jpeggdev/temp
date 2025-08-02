<?php

namespace App\Exception;

class CampaignUpdateException extends UnificationAPIException
{
    public function __construct(string $message = 'Failed to update the campaign.', ?\Throwable $previous = null)
    {
        $fullMessage = "Campaign could not be updated. {$message}";
        parent::__construct($fullMessage, 0, $previous);
    }
}

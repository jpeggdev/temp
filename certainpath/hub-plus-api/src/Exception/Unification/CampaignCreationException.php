<?php

namespace App\Exception\Unification;

use App\Exception\UnificationAPIException;

class CampaignCreationException extends UnificationAPIException
{
    protected int $statusCode = 400;

    public function __construct(string $message = 'Failed to create the campaign.', ?\Exception $previous = null)
    {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

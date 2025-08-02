<?php

namespace App\Exceptions\DomainException\Campaign;

use App\Exceptions\AppException;

class CampaignStopFailedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Campaign could not be stopped.";
    }
}

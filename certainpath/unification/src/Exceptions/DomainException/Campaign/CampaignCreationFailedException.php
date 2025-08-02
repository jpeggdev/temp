<?php

namespace App\Exceptions\DomainException\Campaign;

use App\Exceptions\AppException;

class CampaignCreationFailedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Campaign could not be created.";
    }
}

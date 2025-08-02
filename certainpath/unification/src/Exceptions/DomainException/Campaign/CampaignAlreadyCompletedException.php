<?php

namespace App\Exceptions\DomainException\Campaign;

use App\Exceptions\AppException;

class CampaignAlreadyCompletedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "The campaign creation process cannot proceed because this campaign has already been completed.";
    }
}

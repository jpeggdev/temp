<?php

namespace App\Exceptions\DomainException\Campaign;

use App\Exceptions\AppException;

class CampaignAlreadyProcessingException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "The campaign creation process cannot be initiated since another instance of the same campaign is currently being processed.";
    }
}

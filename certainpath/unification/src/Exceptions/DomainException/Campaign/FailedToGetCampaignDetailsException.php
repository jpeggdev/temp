<?php

namespace App\Exceptions\DomainException\Campaign;

use App\Exceptions\AppException;

class FailedToGetCampaignDetailsException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Failed to get campaign details.";
    }
}

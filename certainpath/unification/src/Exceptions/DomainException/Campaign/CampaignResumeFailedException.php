<?php

namespace App\Exceptions\DomainException\Campaign;

use App\Exceptions\AppException;

class CampaignResumeFailedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return "Campaign could not be resumed. " . $this->getMessage();
    }
}

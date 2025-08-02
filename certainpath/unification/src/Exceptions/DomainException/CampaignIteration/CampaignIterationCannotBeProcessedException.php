<?php

namespace App\Exceptions\DomainException\CampaignIteration;

use App\Exceptions\AppException;

class CampaignIterationCannotBeProcessedException extends AppException
{

    protected function getDefaultMessage(): string
    {
        return "Campaign Iteration cannot be created.";
    }
}

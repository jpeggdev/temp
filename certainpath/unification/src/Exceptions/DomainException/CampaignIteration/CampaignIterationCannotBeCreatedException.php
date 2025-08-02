<?php

namespace App\Exceptions\DomainException\CampaignIteration;

use App\Exceptions\AppException;

class CampaignIterationCannotBeCreatedException extends AppException
{

    protected function getDefaultMessage(): string
    {
        return sprintf(
            "Campaign Iteration cannot be created. %s",
            $this->getMessage()
        );
    }
}

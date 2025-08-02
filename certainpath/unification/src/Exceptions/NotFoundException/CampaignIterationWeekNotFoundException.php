<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CampaignIterationWeekNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Campaign Iteration Week was not found.';
    }
}

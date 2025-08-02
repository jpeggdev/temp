<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CampaignIterationStatusNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Campaign iteration status was not found.';
    }
}

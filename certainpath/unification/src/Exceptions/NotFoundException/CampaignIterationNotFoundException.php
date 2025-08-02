<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CampaignIterationNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Campaign iteration was not found.';
    }
}

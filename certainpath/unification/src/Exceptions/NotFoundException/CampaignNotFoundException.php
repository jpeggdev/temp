<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CampaignNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Campaign was not found.';
    }
}

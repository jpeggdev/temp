<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CampaignEventNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Campaign event was not found.';
    }
}

<?php

namespace App\Exceptions\NotFoundException;

use App\Exceptions\AppException;

class CampaignStatusNotFoundException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Campaign Status was not found.';
    }
}

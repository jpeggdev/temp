<?php

namespace App\Exceptions\Smarty;

use App\Exceptions\AppException;

class RequestAddressCandidateFailedException extends AppException
{
    protected function getDefaultMessage(): string
    {
        return 'Smarty API: Failed to request address candidate.';
    }
}

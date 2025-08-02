<?php

namespace App\Exceptions\Smarty;

use App\Exceptions\AppException;

class NoAddressCandidateFoundException extends AppException
{

    protected function getDefaultMessage(): string
    {
        return 'Smarty API: No valid address candidate found.';
    }
}

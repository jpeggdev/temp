<?php

declare(strict_types=1);

namespace App\Module\EventRegistration\Feature\EventRegistration\Exception;

class NoEventSessionFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('No EventSession found for this checkout.');
    }
}

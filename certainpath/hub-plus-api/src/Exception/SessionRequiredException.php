<?php

declare(strict_types=1);

namespace App\Exception;

class SessionRequiredException extends \InvalidArgumentException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('Session ID is required', 400, $previous);
    }
}

<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EventAccessDeniedException extends AccessDeniedHttpException
{
    public function __construct(string $message = 'You do not have permission to access this event', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}

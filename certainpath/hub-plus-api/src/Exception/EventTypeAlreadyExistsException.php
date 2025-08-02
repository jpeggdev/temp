<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EventTypeAlreadyExistsException extends ConflictHttpException
{
    public function __construct(string $message = 'Event type already exists.', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}

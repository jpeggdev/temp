<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class EventInstructorAlreadyExistsException extends ConflictHttpException
{
    public function __construct(string $message = 'Event instructor already exists.', ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message, $previous, $code);
    }
}

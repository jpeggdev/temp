<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CannotDeleteEventTypeException extends ConflictHttpException
{
    /** @var int */
    protected $code = 409; // HTTP Conflict status code
    protected string $defaultMessage = 'Cannot delete event type because it is being used by one or more events';

    public function __construct(?string $message = null, ?\Throwable $previous = null, int $code = 0)
    {
        parent::__construct($message ?? $this->defaultMessage, $previous, $code ?: $this->code);
    }
}

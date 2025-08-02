<?php

declare(strict_types=1);

namespace App\Exception;

class SessionNotFoundException extends \InvalidArgumentException
{
    public function __construct(int $sessionId, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Event session with ID %d not found', $sessionId), 404, $previous);
    }
}

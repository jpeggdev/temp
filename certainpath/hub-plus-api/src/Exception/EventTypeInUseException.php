<?php

declare(strict_types=1);

namespace App\Exception;

class EventTypeInUseException extends \RuntimeException
{
    public function __construct(string $operation)
    {
        parent::__construct(sprintf('Cannot %s event type because it is in use', $operation));
    }
}

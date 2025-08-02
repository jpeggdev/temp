<?php

declare(strict_types=1);

namespace App\Exception;

class EnrolledStatusNotFoundException extends \RuntimeException
{
    public function __construct(string $message = 'Enrolled status not found', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}

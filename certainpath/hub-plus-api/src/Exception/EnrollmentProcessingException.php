<?php

declare(strict_types=1);

namespace App\Exception;

class EnrollmentProcessingException extends \Exception
{
    public function __construct(string $message = 'An error occurred while processing the enrollment', ?\Throwable $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}

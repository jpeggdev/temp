<?php

declare(strict_types=1);

namespace App\Exception;

class UnableToDeleteIdentityException extends \Exception
{
    public function __construct(int $code, string $message = '', ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

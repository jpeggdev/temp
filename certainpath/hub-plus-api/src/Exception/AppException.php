<?php

namespace App\Exception;

abstract class AppException extends \Exception
{
    public function __construct(?string $message = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($this->getDefaultMessage().' '.$message, $code, $previous);
    }

    abstract protected function getDefaultMessage(): string;
}

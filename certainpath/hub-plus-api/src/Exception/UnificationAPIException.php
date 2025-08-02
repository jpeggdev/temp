<?php

namespace App\Exception;

class UnificationAPIException extends \Exception implements HttpExceptionInterface
{
    protected int $statusCode = 500;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

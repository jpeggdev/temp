<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class UnsupportedSendOptionException extends UnificationAPIException
{
    protected int $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(
        string $message = 'Unsupported send option',
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

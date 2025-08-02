<?php

namespace App\Exception\Unification;

use App\Exception\UnificationAPIException;
use Symfony\Component\HttpFoundation\Response;

class LocationCreationException extends UnificationAPIException
{
    protected int $statusCode = Response::HTTP_BAD_REQUEST;

    public function __construct(
        string $message = 'Failed to create the location.',
        ?\Exception $previous = null,
    ) {
        parent::__construct($message, $this->statusCode, $previous);
    }
}

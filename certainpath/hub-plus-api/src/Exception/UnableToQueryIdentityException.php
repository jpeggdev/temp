<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnableToQueryIdentityException extends HttpException
{
    public function __construct(
        int $statusCode = 500,
        string $message = 'Unable to query identity from Auth0',
        ?\Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}

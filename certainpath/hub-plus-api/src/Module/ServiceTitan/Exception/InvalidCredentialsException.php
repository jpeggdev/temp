<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when ServiceTitan client credentials are invalid.
 *
 * This includes cases where the client ID or client secret are malformed,
 * expired, or do not match valid ServiceTitan application credentials.
 */
class InvalidCredentialsException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan client credentials are invalid.';
    }
}

<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when access token has expired and refresh attempt failed.
 *
 * This is a specific case where the access token is expired and either
 * no refresh token is available or the refresh token has also expired.
 */
class TokenExpiredException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan access token expired and refresh failed.';
    }
}

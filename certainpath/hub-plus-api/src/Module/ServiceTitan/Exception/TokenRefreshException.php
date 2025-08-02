<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when attempting to refresh a ServiceTitan access token fails.
 *
 * This typically occurs when the refresh token has expired, been revoked,
 * or the token refresh endpoint returns an error.
 */
class TokenRefreshException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    protected function getDefaultMessage(): string
    {
        return 'Failed to refresh ServiceTitan access token.';
    }
}

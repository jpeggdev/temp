<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when the ServiceTitan OAuth authorization flow fails.
 *
 * This covers failures during the initial authorization handshake,
 * including authorization code exchange failures and callback processing errors.
 */
class OAuthHandshakeException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan OAuth handshake failed.';
    }
}

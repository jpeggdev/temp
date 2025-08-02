<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when ServiceTitan API returns an error during OAuth operations.
 *
 * This covers general API communication failures, service unavailability,
 * and unexpected API responses during OAuth flows.
 */
class ServiceTitanApiException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_GATEWAY;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan API error during OAuth operation.';
    }
}

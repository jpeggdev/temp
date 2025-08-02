<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when ServiceTitan API rate limits are exceeded during OAuth operations.
 *
 * This occurs when too many OAuth requests are made within a specific time window,
 * and the ServiceTitan API responds with rate limiting headers or error codes.
 */
class RateLimitExceededException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_TOO_MANY_REQUESTS;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan OAuth rate limit exceeded.';
    }
}

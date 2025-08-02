<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when environment-specific configuration issues prevent OAuth operations.
 *
 * This includes missing environment variables, invalid environment settings,
 * or configuration mismatches between development, staging, and production environments.
 */
class EnvironmentConfigurationException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan environment configuration error.';
    }
}

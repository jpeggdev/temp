<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception thrown when credential validation fails.
 *
 * This occurs when stored credentials fail validation checks,
 * such as format validation, encryption/decryption issues,
 * or credential integrity verification.
 */
class CredentialValidationException extends ServiceTitanOAuthException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan credential validation failed.';
    }
}

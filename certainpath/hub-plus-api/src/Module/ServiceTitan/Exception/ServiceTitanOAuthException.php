<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Exception;

use App\Exception\AppException;
use App\Exception\HttpExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base exception for ServiceTitan OAuth-related errors.
 *
 * This class provides a foundation for all ServiceTitan OAuth exceptions,
 * including structured error context, actionable messages, and HTTP status codes.
 */
abstract class ServiceTitanOAuthException extends AppException implements HttpExceptionInterface
{
    public function __construct(
        string $message,
        private readonly ?string $actionableMessage = null,
        /** @var array<string, mixed> */
        private readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get actionable message that provides guidance on how to resolve the error.
     */
    public function getActionableMessage(): ?string
    {
        return $this->actionableMessage;
    }

    /**
     * Get context data for debugging and logging purposes.
     * Context data is sanitized to prevent exposure of sensitive information.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get HTTP status code for API responses.
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected function getDefaultMessage(): string
    {
        return 'ServiceTitan OAuth operation failed.';
    }
}

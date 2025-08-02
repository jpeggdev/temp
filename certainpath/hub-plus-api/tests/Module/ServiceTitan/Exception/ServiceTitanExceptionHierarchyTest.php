<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Exception;

use App\Exception\HttpExceptionInterface;
use App\Module\ServiceTitan\Exception\CredentialValidationException;
use App\Module\ServiceTitan\Exception\EnvironmentConfigurationException;
use App\Module\ServiceTitan\Exception\InvalidCredentialsException;
use App\Module\ServiceTitan\Exception\OAuthHandshakeException;
use App\Module\ServiceTitan\Exception\RateLimitExceededException;
use App\Module\ServiceTitan\Exception\ServiceTitanApiException;
use App\Module\ServiceTitan\Exception\ServiceTitanOAuthException;
use App\Module\ServiceTitan\Exception\TokenExpiredException;
use App\Module\ServiceTitan\Exception\TokenRefreshException;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\HttpFoundation\Response;

class ServiceTitanExceptionHierarchyTest extends AbstractKernelTestCase
{
    public function testServiceTitanOAuthExceptionIsBaseClass(): void
    {
        $exception = new class ('Test message') extends ServiceTitanOAuthException {
            protected function getDefaultMessage(): string
            {
                return 'Test default message';
            }
        };

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertInstanceOf(HttpExceptionInterface::class, $exception);
        self::assertSame('Test default message Test message', $exception->getMessage());
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
        self::assertNull($exception->getActionableMessage());
        self::assertSame([], $exception->getContext());
    }

    public function testServiceTitanOAuthExceptionWithActionableMessageAndContext(): void
    {
        $actionableMessage = 'Please check your credentials';
        $context = ['clientId' => 'masked_***', 'attempt' => 1];

        $exception = new class ('Test message', $actionableMessage, $context) extends ServiceTitanOAuthException {
            protected function getDefaultMessage(): string
            {
                return 'Test default message';
            }
        };

        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testInvalidCredentialsException(): void
    {
        $actionableMessage = 'Please verify your Client ID and Client Secret in the ServiceTitan Developer Portal';
        $context = ['clientId' => 'masked_client_id'];

        $exception = new InvalidCredentialsException(
            'ServiceTitan client credentials are invalid',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan client credentials are invalid. ServiceTitan client credentials are invalid', $exception->getMessage());
        self::assertSame(Response::HTTP_UNAUTHORIZED, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testTokenRefreshException(): void
    {
        $actionableMessage = 'The refresh token may be expired. Please re-authorize your ServiceTitan integration';
        $context = ['credentialId' => 'test-credential-id'];

        $exception = new TokenRefreshException(
            'Failed to refresh ServiceTitan access token',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('Failed to refresh ServiceTitan access token. Failed to refresh ServiceTitan access token', $exception->getMessage());
        self::assertSame(Response::HTTP_UNAUTHORIZED, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testOAuthHandshakeException(): void
    {
        $actionableMessage = 'Check your ServiceTitan app configuration and ensure the integration is approved';
        $context = ['error' => 'invalid_grant', 'statusCode' => 400];

        $exception = new OAuthHandshakeException(
            'ServiceTitan OAuth handshake failed',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan OAuth handshake failed. ServiceTitan OAuth handshake failed', $exception->getMessage());
        self::assertSame(Response::HTTP_BAD_REQUEST, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testCredentialValidationException(): void
    {
        $actionableMessage = 'Please re-enter your ServiceTitan credentials';
        $context = ['validation_errors' => ['clientSecret' => 'invalid format']];

        $exception = new CredentialValidationException(
            'ServiceTitan credential validation failed',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan credential validation failed. ServiceTitan credential validation failed', $exception->getMessage());
        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testTokenExpiredException(): void
    {
        $actionableMessage = 'Please re-authorize your ServiceTitan integration to obtain new tokens';
        $context = ['expired_at' => '2024-01-15T10:30:00Z'];

        $exception = new TokenExpiredException(
            'ServiceTitan access token expired and refresh failed',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan access token expired and refresh failed. ServiceTitan access token expired and refresh failed', $exception->getMessage());
        self::assertSame(Response::HTTP_UNAUTHORIZED, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testServiceTitanApiException(): void
    {
        $actionableMessage = 'ServiceTitan API is currently unavailable. Please try again later';
        $context = ['statusCode' => 503, 'endpoint' => '/oauth/token'];

        $exception = new ServiceTitanApiException(
            'ServiceTitan API error during OAuth operation',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan API error during OAuth operation. ServiceTitan API error during OAuth operation', $exception->getMessage());
        self::assertSame(Response::HTTP_BAD_GATEWAY, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testRateLimitExceededException(): void
    {
        $actionableMessage = 'Too many OAuth requests. Please wait before retrying';
        $context = ['retry_after' => 60, 'requests_remaining' => 0];

        $exception = new RateLimitExceededException(
            'ServiceTitan OAuth rate limit exceeded',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan OAuth rate limit exceeded. ServiceTitan OAuth rate limit exceeded', $exception->getMessage());
        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testEnvironmentConfigurationException(): void
    {
        $actionableMessage = 'Check your ServiceTitan environment configuration settings';
        $context = ['missing_vars' => ['SERVICETITAN_CLIENT_ID', 'SERVICETITAN_CLIENT_SECRET']];

        $exception = new EnvironmentConfigurationException(
            'ServiceTitan environment configuration error',
            $actionableMessage,
            $context
        );

        self::assertInstanceOf(ServiceTitanOAuthException::class, $exception);
        self::assertSame('ServiceTitan environment configuration error. ServiceTitan environment configuration error', $exception->getMessage());
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getStatusCode());
        self::assertSame($actionableMessage, $exception->getActionableMessage());
        self::assertSame($context, $exception->getContext());
    }

    public function testAllExceptionsImplementHttpExceptionInterface(): void
    {
        /** @var array<class-string> $exceptions */
        $exceptions = [
            InvalidCredentialsException::class,
            TokenRefreshException::class,
            OAuthHandshakeException::class,
            CredentialValidationException::class,
            TokenExpiredException::class,
            ServiceTitanApiException::class,
            RateLimitExceededException::class,
            EnvironmentConfigurationException::class,
        ];

        foreach ($exceptions as $exceptionClass) {
            $exception = new $exceptionClass('Test message');
            self::assertInstanceOf(HttpExceptionInterface::class, $exception, $exceptionClass.' must implement HttpExceptionInterface');
            self::assertIsInt($exception->getStatusCode(), $exceptionClass.' must return integer status code');
        }
    }

    public function testExceptionStatusCodesAreAppropriate(): void
    {
        /** @var array<class-string, int> $expectations */
        $expectations = [
            InvalidCredentialsException::class => Response::HTTP_UNAUTHORIZED,
            TokenRefreshException::class => Response::HTTP_UNAUTHORIZED,
            OAuthHandshakeException::class => Response::HTTP_BAD_REQUEST,
            CredentialValidationException::class => Response::HTTP_UNPROCESSABLE_ENTITY,
            TokenExpiredException::class => Response::HTTP_UNAUTHORIZED,
            ServiceTitanApiException::class => Response::HTTP_BAD_GATEWAY,
            RateLimitExceededException::class => Response::HTTP_TOO_MANY_REQUESTS,
            EnvironmentConfigurationException::class => Response::HTTP_INTERNAL_SERVER_ERROR,
        ];

        foreach ($expectations as $exceptionClass => $expectedStatusCode) {
            $exception = new $exceptionClass('Test message');
            self::assertSame($expectedStatusCode, $exception->getStatusCode(), $exceptionClass.' has incorrect status code');
        }
    }

    public function testExceptionMessagesIncludeDefaultMessages(): void
    {
        /** @var array<class-string, string> $defaultMessages */
        $defaultMessages = [
            InvalidCredentialsException::class => 'ServiceTitan client credentials are invalid.',
            TokenRefreshException::class => 'Failed to refresh ServiceTitan access token.',
            OAuthHandshakeException::class => 'ServiceTitan OAuth handshake failed.',
            CredentialValidationException::class => 'ServiceTitan credential validation failed.',
            TokenExpiredException::class => 'ServiceTitan access token expired and refresh failed.',
            ServiceTitanApiException::class => 'ServiceTitan API error during OAuth operation.',
            RateLimitExceededException::class => 'ServiceTitan OAuth rate limit exceeded.',
            EnvironmentConfigurationException::class => 'ServiceTitan environment configuration error.',
        ];

        foreach ($defaultMessages as $exceptionClass => $expectedDefaultMessage) {
            $exception = new $exceptionClass('Custom message');
            self::assertStringContainsString($expectedDefaultMessage, $exception->getMessage(), $exceptionClass.' does not include default message');
        }
    }

    /**
     * Test exception creation examples from the story requirements.
     */
    public function testStoryExampleUsage(): void
    {
        // Example 1: InvalidCredentialsException
        $exception1 = new InvalidCredentialsException(
            'ServiceTitan client credentials are invalid',
            'Please verify your Client ID and Client Secret in the ServiceTitan Developer Portal',
            ['clientId' => 'masked_client_id']
        );

        self::assertSame('Please verify your Client ID and Client Secret in the ServiceTitan Developer Portal', $exception1->getActionableMessage());
        self::assertSame(['clientId' => 'masked_client_id'], $exception1->getContext());

        // Example 2: TokenRefreshException
        $exception2 = new TokenRefreshException(
            'Failed to refresh ServiceTitan access token',
            'The refresh token may be expired. Please re-authorize your ServiceTitan integration',
            ['credentialId' => 'test-credential-uuid']
        );

        self::assertSame('The refresh token may be expired. Please re-authorize your ServiceTitan integration', $exception2->getActionableMessage());
        self::assertSame(['credentialId' => 'test-credential-uuid'], $exception2->getContext());

        // Example 3: OAuthHandshakeException
        $exception3 = new OAuthHandshakeException(
            'ServiceTitan OAuth handshake failed',
            'Check your ServiceTitan app configuration and ensure the integration is approved',
            ['error' => 'invalid_grant', 'statusCode' => 400]
        );

        self::assertSame('Check your ServiceTitan app configuration and ensure the integration is approved', $exception3->getActionableMessage());
        self::assertSame(['error' => 'invalid_grant', 'statusCode' => 400], $exception3->getContext());
    }
}

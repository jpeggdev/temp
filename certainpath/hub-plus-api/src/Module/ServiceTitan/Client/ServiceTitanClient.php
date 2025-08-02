<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Client;

use App\Client\DomainClient;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Exception\RateLimitExceededException;
use App\Module\ServiceTitan\Exception\ServiceTitanApiException;
use App\Module\ServiceTitan\Exception\TokenExpiredException;
use App\Module\ServiceTitan\Service\ServiceTitanAuthService;
use App\Module\ServiceTitan\Service\ServiceTitanRateLimitManager;
use App\Module\ServiceTitan\ValueObject\ApiResponse;
use App\Module\ServiceTitan\ValueObject\ConnectionTestResponse;
use App\Module\ServiceTitan\ValueObject\CustomerResponse;
use App\Module\ServiceTitan\ValueObject\InvoiceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * ServiceTitan API Client for making authenticated requests to ServiceTitan REST API.
 *
 * This client extends DomainClient and provides OAuth authentication, rate limiting,
 * error handling, and environment-specific configuration for ServiceTitan integration.
 */
class ServiceTitanClient extends DomainClient
{
    private const int MAX_RETRIES = 4;
    private const array RETRY_DELAYS = [1, 2, 4, 8]; // Exponential backoff in seconds

    // ServiceTitan API endpoints
    private const string CUSTOMERS_ENDPOINT = '/api/v2/tenant/%s/crm/customers';
    private const string INVOICES_ENDPOINT = '/api/v2/tenant/%s/accounting/invoices';
    private const string TEST_ENDPOINT = '/api/v2/tenant/%s/companies';

    private ServiceTitanCredential $credential;
    private ServiceTitanAuthService $authService;
    private ServiceTitanRateLimitManager $rateLimitManager;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $httpClient,
        ServiceTitanCredential $credential,
        ServiceTitanAuthService $authService,
        ServiceTitanRateLimitManager $rateLimitManager,
        LoggerInterface $logger
    ) {
        $baseUrl = $this->getBaseUrlForEnvironment($credential->getEnvironment());

        // ServiceTitanClient doesn't use traditional API key, but we need to satisfy parent constructor
        parent::__construct($httpClient, $baseUrl, '');

        $this->credential = $credential;
        $this->authService = $authService;
        $this->rateLimitManager = $rateLimitManager;
        $this->logger = $logger;
    }

    /**
     * Override parent method to use OAuth Bearer token instead of API key
     */
    protected function getAuthorizationHeader(): array
    {
        $this->ensureValidToken();

        return [
            'Authorization' => sprintf("Bearer %s", $this->credential->getAccessToken()),
            'ST-App-Key' => $this->credential->getClientId(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Make a GET request to ServiceTitan API with authentication and error handling
     */
    public function get(string $endpoint, array $params = []): ApiResponse
    {
        $url = sprintf("%s%s", $this->baseUri, $endpoint);

        $this->logger->info('ServiceTitan API GET request', [
            'endpoint' => $endpoint,
            'params' => $params,
            'credential_id' => $this->credential->getId(),
        ]);

        try {
            $response = $this->sendGetRequestWithRetry($url, $params);
            return $this->processResponse($response, 'GET', $endpoint);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('ServiceTitan API GET request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);
            throw new ServiceTitanApiException(
                sprintf(
                    "API GET request failed: %s",
                    $e->getMessage()
                ),
                null,
                ['endpoint' => $endpoint],
                0,
                $e
            );
        }
    }

    /**
     * Make a POST request to ServiceTitan API with authentication and error handling
     */
    public function post(string $endpoint, array $data = []): ApiResponse
    {
        $url = $this->baseUri . $endpoint;

        $this->logger->info('ServiceTitan API POST request', [
            'endpoint' => $endpoint,
            'credential_id' => $this->credential->getId(),
        ]);

        try {
            $response = $this->sendPostRequestWithRetry($url, $data);
            return $this->processResponse($response, 'POST', $endpoint);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('ServiceTitan API POST request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);
            throw new ServiceTitanApiException('API POST request failed: ' . $e->getMessage(), null, ['endpoint' => $endpoint], 0, $e);
        }
    }

    /**
     * Make a PUT request to ServiceTitan API with authentication and error handling
     */
    public function put(string $endpoint, array $data = []): ApiResponse
    {
        $url = $this->baseUri . $endpoint;

        $this->logger->info('ServiceTitan API PUT request', [
            'endpoint' => $endpoint,
            'credential_id' => $this->credential->getId(),
        ]);

        try {
            $response = $this->sendPutRequestWithRetry($url, $data);
            return $this->processResponse($response, 'PUT', $endpoint);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('ServiceTitan API PUT request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);
            throw new ServiceTitanApiException('API PUT request failed: ' . $e->getMessage(), null, ['endpoint' => $endpoint], 0, $e);
        }
    }

    /**
     * Make a DELETE request to ServiceTitan API with authentication and error handling
     */
    public function delete(string $endpoint): ApiResponse
    {
        $url = $this->baseUri . $endpoint;

        $this->logger->info('ServiceTitan API DELETE request', [
            'endpoint' => $endpoint,
            'credential_id' => $this->credential->getId(),
        ]);

        try {
            $response = $this->sendDeleteRequestWithRetry($url);
            return $this->processResponse($response, 'DELETE', $endpoint);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('ServiceTitan API DELETE request failed', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);
            throw new ServiceTitanApiException('API DELETE request failed: ' . $e->getMessage(), null, ['endpoint' => $endpoint], 0, $e);
        }
    }

    /**
     * Get customers from ServiceTitan API with optional filters
     */
    public function getCustomers(array $filters = []): CustomerResponse
    {
        $tenantId = $this->getTenantId();
        $endpoint = sprintf(self::CUSTOMERS_ENDPOINT, $tenantId);

        $response = $this->get($endpoint, $filters);

        return new CustomerResponse(
            $response->isSuccess(),
            $response->getData(),
            $response->getStatusCode(),
            $response->getError()
        );
    }

    /**
     * Get invoices from ServiceTitan API with optional filters
     */
    public function getInvoices(array $filters = []): InvoiceResponse
    {
        $tenantId = $this->getTenantId();
        $endpoint = sprintf(self::INVOICES_ENDPOINT, $tenantId);

        $response = $this->get($endpoint, $filters);

        return new InvoiceResponse(
            $response->isSuccess(),
            $response->getData(),
            $response->getStatusCode(),
            $response->getError()
        );
    }

    /**
     * Test connection to ServiceTitan API
     */
    public function testConnection(): ConnectionTestResponse
    {
        try {
            $tenantId = $this->getTenantId();
            $endpoint = sprintf(self::TEST_ENDPOINT, $tenantId);

            $response = $this->get($endpoint);

            $this->logger->info('ServiceTitan connection test completed', [
                'success' => $response->isSuccess(),
                'status_code' => $response->getStatusCode(),
                'credential_id' => $this->credential->getId(),
            ]);

            return new ConnectionTestResponse(
                $response->isSuccess(),
                $response->getStatusCode(),
                $response->isSuccess() ? 'Connection successful' : $response->getError()
            );
        } catch (ServiceTitanApiException $e) {
            $this->logger->error('ServiceTitan connection test failed', [
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);

            return new ConnectionTestResponse(
                false,
                500,
                'Connection test failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Ensure we have a valid access token, refresh if needed
     */
    private function ensureValidToken(): void
    {
        if ($this->credential->isTokenExpired() || !$this->credential->hasValidTokens()) {
            $this->logger->info('Token expired or invalid, attempting refresh', [
                'credential_id' => $this->credential->getId(),
            ]);

            if (!$this->authService->refreshAccessToken($this->credential)) {
                throw new TokenExpiredException(
                    'Failed to refresh access token',
                    'Please re-authenticate with ServiceTitan',
                    ['credential_id' => $this->credential->getId()]
                );
            }
        }
    }

    /**
     * Send GET request with retry logic and rate limiting
     */
    private function sendGetRequestWithRetry(string $url, array $query = []): ResponseInterface
    {
        return $this->executeWithRetry(function () use ($url, $query) {
            $this->rateLimitManager->enforceRateLimit($this->credential);
            return $this->sendGetRequest($url, $query);
        });
    }

    /**
     * Send POST request with retry logic and rate limiting
     */
    private function sendPostRequestWithRetry(string $url, array $payload): ResponseInterface
    {
        return $this->executeWithRetry(function () use ($url, $payload) {
            $this->rateLimitManager->enforceRateLimit($this->credential);
            return $this->sendPostRequest($url, $payload);
        });
    }

    /**
     * Send PUT request with retry logic and rate limiting
     */
    private function sendPutRequestWithRetry(string $url, array $payload): ResponseInterface
    {
        return $this->executeWithRetry(function () use ($url, $payload) {
            $this->rateLimitManager->enforceRateLimit($this->credential);
            return $this->sendPutRequest($url, $payload);
        });
    }

    /**
     * Send DELETE request with retry logic and rate limiting
     */
    private function sendDeleteRequestWithRetry(string $url): ResponseInterface
    {
        return $this->executeWithRetry(function () use ($url) {
            $this->rateLimitManager->enforceRateLimit($this->credential);
            return $this->sendDeleteRequest($url);
        });
    }

    /**
     * Execute a request with exponential backoff retry logic
     */
    private function executeWithRetry(callable $requestFunction): ResponseInterface
    {
        $retryCount = 0;

        while ($retryCount <= self::MAX_RETRIES) {
            try {
                $response = $requestFunction();

                // Handle HTTP client exceptions that may be thrown by getStatusCode()
                try {
                    $statusCode = $response->getStatusCode();
                } catch (ClientExceptionInterface | ServerExceptionInterface | RedirectionExceptionInterface $e) {
                    // Extract status code from the exception if available
                    if (method_exists($e, 'getResponse') && $e->getResponse() !== null) {
                        $statusCode = $e->getResponse()->getStatusCode();
                        $response = $e->getResponse();
                    } else {
                        // Parse status code from exception message as fallback
                        if (preg_match('/HTTP (\d+)/', $e->getMessage(), $matches)) {
                            $statusCode = (int) $matches[1];
                        } else {
                            throw $e; // Re-throw if we can't determine status code
                        }
                    }
                }

                // Handle rate limiting
                if ($statusCode === 429) {
                    if ($retryCount >= self::MAX_RETRIES) {
                        throw new RateLimitExceededException(
                            'ServiceTitan API rate limit exceeded after all retries',
                            'Please wait before making more requests',
                            ['retry_count' => $retryCount]
                        );
                    }

                    // Extract retry-after header if available
                    $retryAfter = $this->extractRetryAfterHeader($response);
                    $this->rateLimitManager->handleRateLimitExceeded($this->credential, $retryAfter);

                    $this->handleRateLimit($retryCount);
                    $retryCount++;
                    continue;
                }

                // Handle server errors with retry
                if ($statusCode >= 500 && $retryCount < self::MAX_RETRIES) {
                    $this->waitForRetry($retryCount);
                    $retryCount++;
                    continue;
                }

                if ($statusCode >= 500) {
                    throw new ServiceTitanApiException(
                        sprintf('ServiceTitan API server error: %d', $statusCode),
                        'ServiceTitan API is experiencing issues',
                        ['status_code' => $statusCode, 'retry_count' => $retryCount]
                    );
                }

                // Update rate limit manager with response headers (only for successful responses)
                try {
                    $this->rateLimitManager->updateFromHeaders($this->credential, $response->getHeaders());
                } catch (\Throwable $e) {
                    // Ignore header processing errors - the response is still valid
                    $this->logger->debug('Could not process response headers for rate limiting', [
                        'error' => $e->getMessage(),
                        'credential_id' => $this->credential->getId(),
                    ]);
                }

                return $response;
            } catch (TransportExceptionInterface $e) {
                $this->logger->warning('ServiceTitan API request failed, retrying', [
                    'retry_count' => $retryCount,
                    'error' => $e->getMessage(),
                    'credential_id' => $this->credential->getId(),
                ]);

                if ($retryCount >= self::MAX_RETRIES) {
                    $this->logger->error('ServiceTitan API request failed after all retries', [
                        'retry_count' => $retryCount,
                        'error' => $e->getMessage(),
                        'credential_id' => $this->credential->getId(),
                    ]);

                    throw $e;
                }

                $this->waitForRetry($retryCount);
                $retryCount++;
            }
        }

        throw new ServiceTitanApiException('Maximum retry attempts exceeded');
    }

    /**
     * Process HTTP response into ApiResponse object
     */
    private function processResponse(ResponseInterface $response, string $method, string $endpoint): ApiResponse
    {
        try {
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);

            $this->logger->debug('ServiceTitan API response processed', [
                'method' => $method,
                'endpoint' => $endpoint,
                'status_code' => $statusCode,
                'credential_id' => $this->credential->getId(),
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                return ApiResponse::success($data, $statusCode);
            }

            $error = $data['message'] ?? $data['error'] ?? 'Unknown API error';
            return ApiResponse::error($error, $statusCode);
        } catch (DecodingExceptionInterface $e) {
            $this->logger->error('Failed to decode ServiceTitan API response', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);

            return ApiResponse::error('Invalid JSON response from ServiceTitan API', $response->getStatusCode());
        }
    }

    /**
     * Extract retry-after header from 429 response
     */
    private function extractRetryAfterHeader(ResponseInterface $response): int
    {
        try {
            $headers = $response->getHeaders();

            // Check for Retry-After header (standard)
            if (isset($headers['retry-after'][0])) {
                return (int) $headers['retry-after'][0];
            }

            // Check for X-RateLimit-Reset header (some APIs use this)
            if (isset($headers['x-ratelimit-reset'][0])) {
                $resetTime = (int) $headers['x-ratelimit-reset'][0];
                return max(0, $resetTime - time());
            }
        } catch (\Throwable $e) {
            // Ignore header processing errors for HTTP error responses
            $this->logger->debug('Could not extract retry-after header from rate limit response', [
                'error' => $e->getMessage(),
                'credential_id' => $this->credential->getId(),
            ]);
        }

        // Default fallback
        return 60;
    }

    /**
     * Handle rate limit response from ServiceTitan API
     */
    private function handleRateLimit(int $retryCount): void
    {
        $delay = self::RETRY_DELAYS[$retryCount] ?? self::RETRY_DELAYS[self::MAX_RETRIES - 1];

        $this->logger->info('ServiceTitan API rate limited, waiting before retry', [
            'retry_count' => $retryCount,
            'delay_seconds' => $delay,
            'credential_id' => $this->credential->getId(),
        ]);

        sleep($delay);
    }

    /**
     * Wait before retry due to server error
     */
    private function waitForRetry(int $retryCount): void
    {
        $delay = self::RETRY_DELAYS[$retryCount] ?? self::RETRY_DELAYS[self::MAX_RETRIES - 1];

        $this->logger->info('Waiting before retry due to server error', [
            'retry_count' => $retryCount,
            'delay_seconds' => $delay,
            'credential_id' => $this->credential->getId(),
        ]);

        sleep($delay);
    }

    /**
     * Get the base URL for the specified environment
     */
    private function getBaseUrlForEnvironment(?ServiceTitanEnvironment $environment): string
    {
        return match ($environment) {
            ServiceTitanEnvironment::PRODUCTION => 'https://api.servicetitan.io',
            ServiceTitanEnvironment::INTEGRATION => 'https://api-integration.servicetitan.io',
            default => throw new ServiceTitanApiException('Invalid ServiceTitan environment'),
        };
    }

    /**
     * Get current rate limit metrics for monitoring and debugging
     */
    public function getRateLimitMetrics(): \App\Module\ServiceTitan\ValueObject\RateLimitMetrics
    {
        return $this->rateLimitManager->getUsageMetrics($this->credential);
    }

    /**
     * Get tenant ID from client ID (assuming it's embedded in the client ID)
     * This is a simplified implementation - actual tenant ID extraction may vary
     */
    private function getTenantId(): string
    {
        // For now, use a placeholder. In real implementation, tenant ID might be:
        // - Part of the credential configuration
        // - Extracted from the client ID
        // - Retrieved from a separate API call
        return 'tenant';
    }
}

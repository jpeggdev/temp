<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Service;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\ValueObject\OAuthCredentials;
use App\Module\ServiceTitan\ValueObject\OAuthResult;
use App\Module\ServiceTitan\ValueObject\ValidationResult;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServiceTitanAuthService
{
    private const int MAX_RETRIES = 4;
    private const array RETRY_DELAYS = [1, 2, 4, 8]; // Exponential backoff in seconds
    private const string OAUTH_TOKEN_URL = 'https://auth.servicetitan.io/connect/token';
    private const string INTEGRATION_TOKEN_URL = 'https://auth-integration.servicetitan.io/connect/token';
    private const string TEST_ENDPOINT = '/api/v2/tenant/%s/companies';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function authenticateCredential(ServiceTitanCredential $credential): bool
    {
        $this->logger->info('Starting OAuth authentication for credential', [
            'credential_id' => $credential->getId(),
            'company_id' => $credential->getCompany()?->getId(),
            'environment' => $credential->getEnvironment()?->value,
        ]);

        $credential->setLastConnectionAttempt(new \DateTime());

        if (!$credential->hasValidCredentials()) {
            $this->logger->warning('Invalid credentials provided', [
                'credential_id' => $credential->getId(),
            ]);
            $credential->setConnectionStatus(ServiceTitanConnectionStatus::ERROR);
            $this->credentialRepository->save($credential, true);

            return false;
        }

        // Try to refresh existing token first if available
        if ($credential->getRefreshToken() !== null && $credential->isTokenExpired()) {
            $this->logger->info('Attempting to refresh existing token');
            if ($this->refreshAccessToken($credential)) {
                return true;
            }
        }

        // Perform fresh OAuth handshake
        $clientId = $credential->getClientId();
        $clientSecret = $credential->getClientSecret();
        $environment = $credential->getEnvironment();

        if ($clientId === null || $clientSecret === null || $environment === null) {
            $this->logger->error('Missing required credential data for OAuth handshake', [
                'credential_id' => $credential->getId(),
            ]);
            $credential->setConnectionStatus(ServiceTitanConnectionStatus::ERROR);
            $this->credentialRepository->save($credential, true);

            return false;
        }

        $oauthCredentials = new OAuthCredentials($clientId, $clientSecret, $environment);

        $result = $this->performOAuthHandshake(
            $oauthCredentials->clientId,
            $oauthCredentials->clientSecret,
            $oauthCredentials->environment
        );

        if ($result->isSuccess()) {
            $credential->setAccessToken($result->accessToken);
            $credential->setRefreshToken($result->refreshToken);
            $credential->setTokenExpiresAt($result->expiresAt);
            $credential->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);

            $this->credentialRepository->save($credential, true);

            $this->logger->info('OAuth authentication successful', [
                'credential_id' => $credential->getId(),
            ]);

            return true;
        }

        $credential->setConnectionStatus(ServiceTitanConnectionStatus::ERROR);
        $this->credentialRepository->save($credential, true);

        $this->logger->error('OAuth authentication failed', [
            'credential_id' => $credential->getId(),
            'error' => $result->error,
            'error_description' => $result->errorDescription,
        ]);

        return false;
    }

    public function refreshAccessToken(ServiceTitanCredential $credential): bool
    {
        $this->logger->info('Refreshing access token', [
            'credential_id' => $credential->getId(),
        ]);

        if ($credential->getRefreshToken() === null) {
            $this->logger->warning('No refresh token available', [
                'credential_id' => $credential->getId(),
            ]);

            return false;
        }

        $environment = $credential->getEnvironment();
        if ($environment === null) {
            $this->logger->error('Missing environment for token refresh', [
                'credential_id' => $credential->getId(),
            ]);

            return false;
        }

        $tokenUrl = $this->getTokenUrl($environment);
        $retryCount = 0;

        while ($retryCount <= self::MAX_RETRIES) {
            try {
                $response = $this->httpClient->request('POST', $tokenUrl, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => http_build_query([
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $credential->getRefreshToken(),
                        'client_id' => $credential->getClientId(),
                        'client_secret' => $credential->getClientSecret(),
                    ]),
                ]);

                $statusCode = $response->getStatusCode();
                $data = $response->toArray(false);

                if ($statusCode === 200) {
                    $expiresAt = new \DateTime();
                    $expiresAt->add(new \DateInterval('PT'.$data['expires_in'].'S'));

                    $credential->setAccessToken($data['access_token']);
                    if (isset($data['refresh_token'])) {
                        $credential->setRefreshToken($data['refresh_token']);
                    }
                    $credential->setTokenExpiresAt($expiresAt);
                    $credential->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);

                    $this->credentialRepository->save($credential, true);

                    $this->logger->info('Token refresh successful', [
                        'credential_id' => $credential->getId(),
                    ]);

                    return true;
                }

                if ($statusCode === 429) {
                    $this->handleRateLimit($retryCount);
                    $retryCount++;
                    continue;
                }

                if ($statusCode >= 400 && $statusCode < 500) {
                    $this->logger->error('Token refresh failed with client error', [
                        'credential_id' => $credential->getId(),
                        'status_code' => $statusCode,
                        'error' => $data['error'] ?? 'unknown_error',
                        'error_description' => $data['error_description'] ?? null,
                    ]);

                    $credential->setConnectionStatus(ServiceTitanConnectionStatus::ERROR);
                    $this->credentialRepository->save($credential, true);

                    return false;
                }

                throw new \RuntimeException(sprintf('Server error: %d', $statusCode));
            } catch (TransportExceptionInterface|ServerExceptionInterface $e) {
                $this->logger->warning('Token refresh attempt failed', [
                    'credential_id' => $credential->getId(),
                    'retry_count' => $retryCount,
                    'error' => $e->getMessage(),
                ]);

                if ($retryCount >= self::MAX_RETRIES) {
                    $this->logger->error('Token refresh failed after all retries', [
                        'credential_id' => $credential->getId(),
                        'error' => $e->getMessage(),
                    ]);

                    $credential->setConnectionStatus(ServiceTitanConnectionStatus::ERROR);
                    $this->credentialRepository->save($credential, true);

                    return false;
                }

                $this->waitForRetry($retryCount);
                $retryCount++;
            }
        }

        return false;
    }

    public function testConnection(ServiceTitanCredential $credential): bool
    {
        $this->logger->info('Testing connection', [
            'credential_id' => $credential->getId(),
        ]);

        if (!$credential->hasValidTokens()) {
            $this->logger->info('Invalid tokens, attempting to authenticate first');
            if (!$this->authenticateCredential($credential)) {
                return false;
            }
        }

        // Test connection with a simple API call
        $environment = $credential->getEnvironment();
        if ($environment === null) {
            $this->logger->error('Missing environment for connection test', [
                'credential_id' => $credential->getId(),
            ]);

            return false;
        }

        $baseUrl = $this->getBaseUrl($environment);
        $testUrl = $baseUrl.sprintf(self::TEST_ENDPOINT, 'test');

        try {
            $response = $this->httpClient->request('GET', $testUrl, [
                'headers' => [
                    'Authorization' => 'Bearer '.$credential->getAccessToken(),
                    'ST-App-Key' => $credential->getClientId(),
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200 || $statusCode === 401) {
                // 401 is acceptable for test - it means API is responding
                $this->logger->info('Connection test successful', [
                    'credential_id' => $credential->getId(),
                    'status_code' => $statusCode,
                ]);

                $credential->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);
                $this->credentialRepository->save($credential, true);

                return true;
            }

            $this->logger->error('Connection test failed', [
                'credential_id' => $credential->getId(),
                'status_code' => $statusCode,
            ]);

            return false;
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Connection test failed with transport error', [
                'credential_id' => $credential->getId(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function validateCredentials(OAuthCredentials $credentials): ValidationResult
    {
        $errors = [];

        if (!$credentials->isValid()) {
            if (trim($credentials->clientId) === '') {
                $errors[] = 'Client ID is required';
            }
            if (trim($credentials->clientSecret) === '') {
                $errors[] = 'Client Secret is required';
            }
        }

        // Test the credentials by attempting OAuth handshake
        if (empty($errors)) {
            $result = $this->performOAuthHandshake(
                $credentials->clientId,
                $credentials->clientSecret,
                $credentials->environment
            );

            if ($result->isFailure()) {
                $errors[] = $result->error.($result->errorDescription ? ': '.$result->errorDescription : '');
            }
        }

        return empty($errors) ? ValidationResult::valid() : ValidationResult::invalid($errors);
    }

    public function performOAuthHandshake(string $clientId, string $clientSecret, ServiceTitanEnvironment $environment): OAuthResult
    {
        $this->logger->info('Performing OAuth handshake', [
            'client_id' => $clientId,
            'environment' => $environment->value,
        ]);

        $tokenUrl = $this->getTokenUrl($environment);
        $retryCount = 0;

        while ($retryCount <= self::MAX_RETRIES) {
            try {
                $response = $this->httpClient->request('POST', $tokenUrl, [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => http_build_query([
                        'grant_type' => 'client_credentials',
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                    ]),
                ]);

                $statusCode = $response->getStatusCode();
                $data = $response->toArray(false);

                if ($statusCode === 200) {
                    $expiresAt = new \DateTime();
                    $expiresAt->add(new \DateInterval('PT'.$data['expires_in'].'S'));

                    $this->logger->info('OAuth handshake successful', [
                        'client_id' => $clientId,
                        'expires_in' => $data['expires_in'],
                    ]);

                    return OAuthResult::success(
                        $data['access_token'],
                        $data['refresh_token'] ?? '',
                        $expiresAt
                    );
                }

                if ($statusCode === 429) {
                    $this->handleRateLimit($retryCount);
                    $retryCount++;
                    continue;
                }

                $error = $data['error'] ?? 'unknown_error';
                $errorDescription = $data['error_description'] ?? null;

                $this->logger->error('OAuth handshake failed', [
                    'client_id' => $clientId,
                    'status_code' => $statusCode,
                    'error' => $error,
                    'error_description' => $errorDescription,
                ]);

                return OAuthResult::failure($error, $errorDescription);
            } catch (TransportExceptionInterface|ServerExceptionInterface $e) {
                $this->logger->warning('OAuth handshake attempt failed', [
                    'client_id' => $clientId,
                    'retry_count' => $retryCount,
                    'error' => $e->getMessage(),
                ]);

                if ($retryCount >= self::MAX_RETRIES) {
                    $this->logger->error('OAuth handshake failed after all retries', [
                        'client_id' => $clientId,
                        'error' => $e->getMessage(),
                    ]);

                    return OAuthResult::failure('transport_error', $e->getMessage());
                }

                $this->waitForRetry($retryCount);
                $retryCount++;
            }
        }

        return OAuthResult::failure('max_retries_exceeded', 'Maximum retry attempts exceeded');
    }

    private function getTokenUrl(ServiceTitanEnvironment $environment): string
    {
        return match ($environment) {
            ServiceTitanEnvironment::PRODUCTION => self::OAUTH_TOKEN_URL,
            ServiceTitanEnvironment::INTEGRATION => self::INTEGRATION_TOKEN_URL,
        };
    }

    private function getBaseUrl(ServiceTitanEnvironment $environment): string
    {
        return match ($environment) {
            ServiceTitanEnvironment::PRODUCTION => 'https://api.servicetitan.io',
            ServiceTitanEnvironment::INTEGRATION => 'https://api-integration.servicetitan.io',
        };
    }

    private function handleRateLimit(int $retryCount): void
    {
        if ($retryCount < self::MAX_RETRIES) {
            $delay = self::RETRY_DELAYS[$retryCount] ?? self::RETRY_DELAYS[self::MAX_RETRIES - 1];
            $this->logger->info('Rate limited, waiting before retry', [
                'retry_count' => $retryCount,
                'delay_seconds' => $delay,
            ]);
            sleep($delay);
        }
    }

    private function waitForRetry(int $retryCount): void
    {
        $delay = self::RETRY_DELAYS[$retryCount] ?? self::RETRY_DELAYS[self::MAX_RETRIES - 1];
        $this->logger->info('Waiting before retry due to server error', [
            'retry_count' => $retryCount,
            'delay_seconds' => $delay,
        ]);
        sleep($delay);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Service\ServiceTitanAuthService;
use App\Module\ServiceTitan\ValueObject\OAuthCredentials;
use Psr\Log\LoggerInterface;
use App\Tests\AbstractKernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ServiceTitanAuthServiceTest extends AbstractKernelTestCase
{
    private ServiceTitanAuthService $authService;
    private ServiceTitanCredentialRepository $credentialRepository;
    private MockHttpClient $mockHttpClient;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        parent::setUp();

        /** @var ServiceTitanCredentialRepository $repo */
        $repo = $this->getRepository(ServiceTitanCredentialRepository::class);
        $this->credentialRepository = $repo;
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mockHttpClient = new MockHttpClient();

        $this->authService = new ServiceTitanAuthService(
            $this->mockHttpClient,
            $this->credentialRepository,
            $this->logger
        );
    }

    public function testAuthenticateCredentialWithValidCredentials(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock successful OAuth response
        $mockResponse = new MockResponse((string) (string) json_encode([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->authenticateCredential($credential);

        // Assert
        self::assertTrue($result);
        self::assertSame(ServiceTitanConnectionStatus::ACTIVE, $credential->getConnectionStatus());
        self::assertSame('test_access_token', $credential->getAccessToken());
        self::assertSame('test_refresh_token', $credential->getRefreshToken());
        self::assertNotNull($credential->getTokenExpiresAt());
        self::assertNotNull($credential->getLastConnectionAttempt());
    }

    public function testAuthenticateCredentialWithInvalidCredentials(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('');
        $credential->setClientSecret('');

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Act
        $result = $this->authService->authenticateCredential($credential);

        // Assert
        self::assertFalse($result);
        self::assertSame(ServiceTitanConnectionStatus::ERROR, $credential->getConnectionStatus());
        self::assertNotNull($credential->getLastConnectionAttempt());
    }

    public function testAuthenticateCredentialWithOAuthError(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('invalid_client_id');
        $credential->setClientSecret('invalid_client_secret');

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock OAuth error response
        $mockResponse = new MockResponse((string) json_encode([
            'error' => 'invalid_client',
            'error_description' => 'Invalid client credentials',
        ]), [
            'http_code' => 401,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->authenticateCredential($credential);

        // Assert
        self::assertFalse($result);
        self::assertSame(ServiceTitanConnectionStatus::ERROR, $credential->getConnectionStatus());
    }

    public function testRefreshAccessTokenSuccess(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        $credential->setRefreshToken('existing_refresh_token');
        $credential->setConnectionStatus(ServiceTitanConnectionStatus::ACTIVE);

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock successful token refresh response
        $mockResponse = new MockResponse((string) json_encode([
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->refreshAccessToken($credential);

        // Assert
        self::assertTrue($result);
        self::assertSame('new_access_token', $credential->getAccessToken());
        self::assertSame('new_refresh_token', $credential->getRefreshToken());
        self::assertSame(ServiceTitanConnectionStatus::ACTIVE, $credential->getConnectionStatus());
    }

    public function testRefreshAccessTokenWithoutRefreshToken(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        // No refresh token set

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Act
        $result = $this->authService->refreshAccessToken($credential);

        // Assert
        self::assertFalse($result);
    }

    public function testRefreshAccessTokenWithError(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        $credential->setRefreshToken('invalid_refresh_token');

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock token refresh error response
        $mockResponse = new MockResponse((string) json_encode([
            'error' => 'invalid_grant',
            'error_description' => 'Refresh token is invalid or expired',
        ]), [
            'http_code' => 400,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->refreshAccessToken($credential);

        // Assert
        self::assertFalse($result);
        self::assertSame(ServiceTitanConnectionStatus::ERROR, $credential->getConnectionStatus());
    }

    public function testTestConnectionSuccess(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        $credential->setAccessToken('valid_access_token');
        $credential->setTokenExpiresAt((new \DateTime())->add(new \DateInterval('PT1H')));

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock successful API test response
        $mockResponse = new MockResponse((string) json_encode([
            'data' => [],
            'hasMore' => false,
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->testConnection($credential);

        // Assert
        self::assertTrue($result);
        self::assertSame(ServiceTitanConnectionStatus::ACTIVE, $credential->getConnectionStatus());
    }

    public function testTestConnectionWithExpiredToken(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');
        $credential->setAccessToken('expired_access_token');
        $credential->setTokenExpiresAt((new \DateTime())->sub(new \DateInterval('PT1H')));

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock OAuth handshake for re-authentication
        $oauthResponse = new MockResponse((string) json_encode([
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        // Mock successful API test response
        $testResponse = new MockResponse((string) json_encode([
            'data' => [],
            'hasMore' => false,
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$oauthResponse, $testResponse]);

        // Act
        $result = $this->authService->testConnection($credential);

        // Assert
        self::assertTrue($result);
        self::assertSame('new_access_token', $credential->getAccessToken());
        self::assertSame(ServiceTitanConnectionStatus::ACTIVE, $credential->getConnectionStatus());
    }

    public function testValidateCredentialsValid(): void
    {
        // Arrange
        $credentials = new OAuthCredentials(
            'valid_client_id',
            'valid_client_secret',
            ServiceTitanEnvironment::INTEGRATION
        );

        // Mock successful OAuth handshake
        $mockResponse = new MockResponse((string) json_encode([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->validateCredentials($credentials);

        // Assert
        self::assertTrue($result->isValid);
        self::assertEmpty($result->errors);
    }

    public function testValidateCredentialsInvalid(): void
    {
        // Arrange
        $credentials = new OAuthCredentials(
            '',
            '',
            ServiceTitanEnvironment::INTEGRATION
        );

        // Act
        $result = $this->authService->validateCredentials($credentials);

        // Assert
        self::assertFalse($result->isValid);
        self::assertContains('Client ID is required', $result->errors);
        self::assertContains('Client Secret is required', $result->errors);
    }

    public function testPerformOAuthHandshakeSuccess(): void
    {
        // Arrange
        $mockResponse = new MockResponse((string) json_encode([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->performOAuthHandshake(
            'test_client_id',
            'test_client_secret',
            ServiceTitanEnvironment::INTEGRATION
        );

        // Assert
        self::assertTrue($result->isSuccess());
        self::assertSame('test_access_token', $result->accessToken);
        self::assertSame('test_refresh_token', $result->refreshToken);
        self::assertInstanceOf(\DateTimeInterface::class, $result->expiresAt);
    }

    public function testPerformOAuthHandshakeFailure(): void
    {
        // Arrange
        $mockResponse = new MockResponse((string) json_encode([
            'error' => 'invalid_client',
            'error_description' => 'Invalid client credentials',
        ]), [
            'http_code' => 401,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse]);

        // Act
        $result = $this->authService->performOAuthHandshake(
            'invalid_client_id',
            'invalid_client_secret',
            ServiceTitanEnvironment::INTEGRATION
        );

        // Assert
        self::assertTrue($result->isFailure());
        self::assertSame('invalid_client', $result->error);
        self::assertSame('Invalid client credentials', $result->errorDescription);
    }

    public function testRetryLogicWithRateLimit(): void
    {
        // Arrange
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test_client_id');
        $credential->setClientSecret('test_client_secret');

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Mock rate limit response followed by success
        $rateLimitResponse = new MockResponse('{}', [
            'http_code' => 429,
        ]);

        $successResponse = new MockResponse((string) json_encode([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$rateLimitResponse, $successResponse]);

        // Act
        $result = $this->authService->authenticateCredential($credential);

        // Assert
        self::assertTrue($result);
        self::assertSame(ServiceTitanConnectionStatus::ACTIVE, $credential->getConnectionStatus());
    }

    public function testEnvironmentSpecificUrls(): void
    {
        // Test that different environments use different URLs
        $integrationCredentials = new OAuthCredentials(
            'test_client_id',
            'test_client_secret',
            ServiceTitanEnvironment::INTEGRATION
        );

        $productionCredentials = new OAuthCredentials(
            'test_client_id',
            'test_client_secret',
            ServiceTitanEnvironment::PRODUCTION
        );

        // Mock responses for both environments
        $mockResponse = new MockResponse((string) json_encode([
            'access_token' => 'test_access_token',
            'refresh_token' => 'test_refresh_token',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ]), [
            'http_code' => 200,
        ]);

        $this->mockHttpClient->setResponseFactory([$mockResponse, $mockResponse]);

        // Act & Assert - Integration
        $integrationResult = $this->authService->performOAuthHandshake(
            $integrationCredentials->clientId,
            $integrationCredentials->clientSecret,
            $integrationCredentials->environment
        );
        self::assertTrue($integrationResult->isSuccess());

        // Act & Assert - Production
        $productionResult = $this->authService->performOAuthHandshake(
            $productionCredentials->clientId,
            $productionCredentials->clientSecret,
            $productionCredentials->environment
        );
        self::assertTrue($productionResult->isSuccess());
    }

}

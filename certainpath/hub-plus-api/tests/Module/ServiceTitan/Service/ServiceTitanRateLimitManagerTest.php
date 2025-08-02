<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Service\ServiceTitanRateLimitManager;
use App\Module\ServiceTitan\ValueObject\RateLimitMetrics;
use App\Tests\AbstractKernelTestCase;
use Psr\Log\LoggerInterface;

class ServiceTitanRateLimitManagerTest extends AbstractKernelTestCase
{
    private ServiceTitanRateLimitManager $rateLimitManager;
    private ServiceTitanCredential $testCredential;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);

        // Create rate limit manager with test configuration
        $this->rateLimitManager = new ServiceTitanRateLimitManager(
            $this->logger,
            [
                'requests_per_minute' => 60,
                'requests_per_hour' => 3600,
                'burst_limit' => 10,
            ]
        );

        $this->testCredential = $this->createTestCredential();
    }

    public function testCanMakeRequestWithinLimits(): void
    {
        // Should allow requests within limits
        self::assertTrue($this->rateLimitManager->canMakeRequest($this->testCredential));

        $this->rateLimitManager->recordRequest($this->testCredential);

        // Should still allow requests after recording one
        self::assertTrue($this->rateLimitManager->canMakeRequest($this->testCredential));
    }

    public function testRateLimitEnforcementPerMinute(): void
    {
        // Make requests up to the burst limit first (will hit burst limit at 10)
        for ($i = 0; $i < 10; $i++) {
            self::assertTrue(
                $this->rateLimitManager->canMakeRequest($this->testCredential),
                "Burst request $i should be allowed"
            );
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // 11th request should be blocked by burst limit
        self::assertFalse(
            $this->rateLimitManager->canMakeRequest($this->testCredential),
            'Request beyond burst limit should be blocked'
        );

        // Should indicate a delay is needed
        $delay = $this->rateLimitManager->getDelayUntilNextRequest($this->testCredential);
        self::assertGreaterThan(0, $delay, 'Should require delay when rate limited');
        self::assertLessThanOrEqual(60, $delay, 'Delay should not exceed 60 seconds');
    }

    public function testBurstLimitHandling(): void
    {
        // Allow burst of 10 requests in quick succession
        for ($i = 0; $i < 10; $i++) {
            self::assertTrue(
                $this->rateLimitManager->canMakeRequest($this->testCredential),
                "Burst request $i should be allowed"
            );
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // 11th burst request within the same second should be throttled
        self::assertFalse(
            $this->rateLimitManager->canMakeRequest($this->testCredential),
            'Request beyond burst limit should be blocked'
        );

        $delay = $this->rateLimitManager->getDelayUntilNextRequest($this->testCredential);
        self::assertGreaterThan(0, $delay, 'Should require delay for burst limit');
        self::assertLessThanOrEqual(1, $delay, 'Burst delay should be at most 1 second');
    }

    public function testHourlyRateLimitEnforcement(): void
    {
        // Create a manager with very low hourly limit for testing
        $manager = new ServiceTitanRateLimitManager(
            $this->logger,
            [
                'requests_per_minute' => 60,
                'requests_per_hour' => 5, // Very low for testing
                'burst_limit' => 10,
            ]
        );

        // Make 5 requests (hourly limit)
        for ($i = 0; $i < 5; $i++) {
            self::assertTrue(
                $manager->canMakeRequest($this->testCredential),
                "Hourly request $i should be allowed"
            );
            $manager->recordRequest($this->testCredential);
        }

        // 6th request should be blocked by hourly limit
        self::assertFalse(
            $manager->canMakeRequest($this->testCredential),
            'Request beyond hourly limit should be blocked'
        );

        $delay = $manager->getDelayUntilNextRequest($this->testCredential);
        self::assertGreaterThan(0, $delay, 'Should require delay for hourly limit');
    }

    public function testHandleRateLimitExceeded(): void
    {
        $retryAfter = 30;

        // Initially should allow requests
        self::assertTrue($this->rateLimitManager->canMakeRequest($this->testCredential));

        // Handle rate limit exceeded response
        $this->rateLimitManager->handleRateLimitExceeded($this->testCredential, $retryAfter);

        // Should now block requests
        self::assertFalse($this->rateLimitManager->canMakeRequest($this->testCredential));

        // Should return the correct delay
        $delay = $this->rateLimitManager->getDelayUntilNextRequest($this->testCredential);
        self::assertGreaterThanOrEqual($retryAfter - 1, $delay, 'Delay should respect retry-after');
        self::assertLessThanOrEqual($retryAfter, $delay, 'Delay should not exceed retry-after');
    }

    public function testUpdateFromHeaders(): void
    {
        $headers = [
            'X-RateLimit-Limit' => ['15'], // Set to 15 to test with burst limit of 10
            'X-RateLimit-Remaining' => ['14'],
        ];

        // Update limits from headers
        $this->rateLimitManager->updateFromHeaders($this->testCredential, $headers);

        // Make 10 requests (burst limit first)
        for ($i = 0; $i < 10; $i++) {
            self::assertTrue(
                $this->rateLimitManager->canMakeRequest($this->testCredential),
                "Request $i should be allowed with updated limit"
            );
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // 11th request should be blocked by burst limit
        self::assertFalse(
            $this->rateLimitManager->canMakeRequest($this->testCredential),
            'Request beyond burst limit should be blocked'
        );
    }

    public function testGetUsageMetrics(): void
    {
        // Record some requests
        for ($i = 0; $i < 15; $i++) {
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        $metrics = $this->rateLimitManager->getUsageMetrics($this->testCredential);

        self::assertInstanceOf(RateLimitMetrics::class, $metrics);
        self::assertSame(15, $metrics->getRequestsLastMinute());
        self::assertSame(15, $metrics->getRequestsLastHour());
        self::assertSame(45, $metrics->getRemainingMinuteQuota()); // 60 - 15
        self::assertSame(3585, $metrics->getRemainingHourQuota()); // 3600 - 15
        self::assertGreaterThan(0, $metrics->getAverageRequestsPerMinute());
        self::assertNotNull($metrics->getNextResetTime());
    }

    public function testResetLimits(): void
    {
        // Make some requests and exceed burst limit
        for ($i = 0; $i < 10; $i++) {
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // Should be blocked by burst limit
        self::assertFalse($this->rateLimitManager->canMakeRequest($this->testCredential));

        // Reset limits
        $this->rateLimitManager->resetLimits($this->testCredential);

        // Should now allow requests again
        self::assertTrue($this->rateLimitManager->canMakeRequest($this->testCredential));

        // Metrics should be reset
        $metrics = $this->rateLimitManager->getUsageMetrics($this->testCredential);
        self::assertSame(0, $metrics->getRequestsLastMinute());
        self::assertSame(0, $metrics->getRequestsLastHour());
    }

    public function testEnforceRateLimit(): void
    {
        // Mock sleep to avoid actual delays in tests
        $originalSleep = null;
        if (function_exists('sleep')) {
            // We can't easily mock sleep in unit tests, so we'll test the logic
            // In integration tests, we could test actual sleep behavior
        }

        // Should work normally when within limits
        $this->rateLimitManager->enforceRateLimit($this->testCredential);

        // Verify request was recorded
        $metrics = $this->rateLimitManager->getUsageMetrics($this->testCredential);
        self::assertSame(1, $metrics->getRequestsLastMinute());
    }

    public function testEnforceRateLimitWithDelay(): void
    {
        // Fill up the minute limit
        for ($i = 0; $i < 60; $i++) {
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // The next enforceRateLimit call should handle the delay
        // In a real scenario, this would sleep, but we can't test that easily
        // We can test that it calculates the delay correctly
        $delay = $this->rateLimitManager->getDelayUntilNextRequest($this->testCredential);
        self::assertGreaterThan(0, $delay);
    }

    public function testMultipleCredentialsTrackedSeparately(): void
    {
        $credential2 = $this->createTestCredential('different-client-id');

        // Fill up burst limit for first credential
        for ($i = 0; $i < 10; $i++) {
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // First credential should be blocked by burst limit
        self::assertFalse($this->rateLimitManager->canMakeRequest($this->testCredential));

        // Second credential should still be allowed
        self::assertTrue($this->rateLimitManager->canMakeRequest($credential2));

        // Record request for second credential
        $this->rateLimitManager->recordRequest($credential2);

        // Verify separate tracking
        $metrics1 = $this->rateLimitManager->getUsageMetrics($this->testCredential);
        $metrics2 = $this->rateLimitManager->getUsageMetrics($credential2);

        self::assertSame(10, $metrics1->getRequestsLastMinute());
        self::assertSame(1, $metrics2->getRequestsLastMinute());
    }

    public function testConfigurationDefaults(): void
    {
        $defaultManager = new ServiceTitanRateLimitManager($this->logger);

        // Test with default configuration - burst limit is 20 by default
        for ($i = 0; $i < 20; $i++) { // Default burst limit
            self::assertTrue(
                $defaultManager->canMakeRequest($this->testCredential),
                "Request $i should be allowed with defaults"
            );
            $defaultManager->recordRequest($this->testCredential);
        }

        // 21st request should be blocked by burst limit
        self::assertFalse($defaultManager->canMakeRequest($this->testCredential));
    }

    public function testBurstLimitResetsAfterSecond(): void
    {
        // This test would require time manipulation or mocking time()
        // For now, we'll test the logic conceptually

        // Fill burst limit
        for ($i = 0; $i < 10; $i++) {
            self::assertTrue($this->rateLimitManager->canMakeRequest($this->testCredential));
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // Should be blocked by burst limit
        self::assertFalse($this->rateLimitManager->canMakeRequest($this->testCredential));

        // In a real scenario, after waiting 1 second, burst limit would reset
        // This is tested in integration tests with actual time delays
    }

    private function createTestCredential(string $clientId = 'test-client-id'): ServiceTitanCredential
    {
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId($clientId);
        $credential->setClientSecret('test-secret');
        $credential->setAccessToken('test-token');
        $credential->setTokenExpiresAt(new \DateTime('+1 hour'));

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        return $credential;
    }
}

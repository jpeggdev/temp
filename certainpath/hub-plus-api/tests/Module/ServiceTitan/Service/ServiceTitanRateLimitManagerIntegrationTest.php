<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Service\ServiceTitanRateLimitManager;
use App\Tests\AbstractKernelTestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration tests for ServiceTitanRateLimitManager that test actual time-based behavior
 */
class ServiceTitanRateLimitManagerIntegrationTest extends AbstractKernelTestCase
{
    private ServiceTitanRateLimitManager $rateLimitManager;
    private ServiceTitanCredential $testCredential;
    private LoggerInterface $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);

        // Create rate limit manager with more permissive configuration for integration tests
        $this->rateLimitManager = new ServiceTitanRateLimitManager(
            $this->logger,
            [
                'requests_per_minute' => 10,  // Lower for faster testing
                'requests_per_hour' => 600,   // Lower for faster testing
                'burst_limit' => 5,           // Lower for testing burst behavior
            ]
        );

        $this->testCredential = $this->createTestCredential();
    }

    public function testRateLimitingWithActualTimeDelays(): void
    {
        // Test burst limit first - should allow 5 requests in same second
        for ($i = 0; $i < 5; $i++) {
            self::assertTrue(
                $this->rateLimitManager->canMakeRequest($this->testCredential),
                "Burst request $i should be allowed"
            );
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // 6th request should be blocked by burst limit
        self::assertFalse(
            $this->rateLimitManager->canMakeRequest($this->testCredential),
            'Should be blocked by burst limit'
        );

        // Wait 1 second for burst limit to reset
        sleep(1);

        // Should now allow requests again (burst limit resets per second)
        self::assertTrue(
            $this->rateLimitManager->canMakeRequest($this->testCredential),
            'Should be allowed after burst limit reset'
        );
        $this->rateLimitManager->recordRequest($this->testCredential);
    }

    public function testMinuteLimitEnforcement(): void
    {
        // Make requests at different seconds to avoid burst limit
        for ($i = 0; $i < 10; $i++) {
            if ($i > 0 && $i % 5 === 0) {
                // Sleep every 5 requests to avoid burst limit
                sleep(1);
            }

            self::assertTrue(
                $this->rateLimitManager->canMakeRequest($this->testCredential),
                "Request $i should be allowed within minute limit"
            );
            $this->rateLimitManager->recordRequest($this->testCredential);
        }

        // Next request should be blocked by minute limit
        sleep(1); // Avoid burst limit
        self::assertFalse(
            $this->rateLimitManager->canMakeRequest($this->testCredential),
            'Should be blocked by minute limit'
        );

        $delay = $this->rateLimitManager->getDelayUntilNextRequest($this->testCredential);
        self::assertGreaterThan(0, $delay, 'Should require delay when minute limit exceeded');
        self::assertLessThanOrEqual(60, $delay, 'Delay should not exceed 60 seconds');
    }

    public function testGetUsageMetricsAccuracy(): void
    {
        // Record 3 requests with delays to avoid burst limit
        for ($i = 0; $i < 3; $i++) {
            $this->rateLimitManager->recordRequest($this->testCredential);
            if ($i < 2) {
                usleep(100000); // 0.1 second delay
            }
        }

        $metrics = $this->rateLimitManager->getUsageMetrics($this->testCredential);

        self::assertSame(3, $metrics->getRequestsLastMinute());
        self::assertSame(3, $metrics->getRequestsLastHour());
        self::assertSame(7, $metrics->getRemainingMinuteQuota()); // 10 - 3
        self::assertSame(597, $metrics->getRemainingHourQuota()); // 600 - 3
        self::assertGreaterThan(0, $metrics->getAverageRequestsPerMinute());
    }

    private function createTestCredential(string $clientId = 'integration-test-client'): ServiceTitanCredential
    {
        $company = new Company();
        $company->setCompanyName('Integration Test Company');
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

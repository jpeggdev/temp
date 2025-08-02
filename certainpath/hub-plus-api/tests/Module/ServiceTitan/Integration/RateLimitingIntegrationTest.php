<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\Integration;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Enum\ServiceTitanEnvironment;
use App\Module\ServiceTitan\Service\ServiceTitanRateLimitManager;
use App\Tests\AbstractKernelTestCase;
use Psr\Log\LoggerInterface;

/**
 * Integration test to verify the complete rate limiting system works end-to-end
 */
class RateLimitingIntegrationTest extends AbstractKernelTestCase
{
    public function testRateLimitManagerServiceIsConfiguredCorrectly(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $rateLimitManager = new ServiceTitanRateLimitManager(
            $logger,
            [
                'requests_per_minute' => 120,
                'requests_per_hour' => 3600,
                'burst_limit' => 20,
            ]
        );

        self::assertInstanceOf(ServiceTitanRateLimitManager::class, $rateLimitManager);

        // Create a test credential
        $company = new Company();
        $company->setCompanyName('Test Company');
        $this->getEntityManager()->persist($company);

        $credential = new ServiceTitanCredential();
        $credential->setCompany($company);
        $credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
        $credential->setClientId('test-client-id');
        $credential->setClientSecret('test-secret');
        $credential->setAccessToken('test-token');
        $credential->setTokenExpiresAt(new \DateTime('+1 hour'));

        $this->getEntityManager()->persist($credential);
        $this->getEntityManager()->flush();

        // Test basic rate limiting functionality
        self::assertTrue($rateLimitManager->canMakeRequest($credential));

        $rateLimitManager->recordRequest($credential);

        // Should still allow requests after recording one
        self::assertTrue($rateLimitManager->canMakeRequest($credential));

        // Test metrics
        $metrics = $rateLimitManager->getUsageMetrics($credential);
        self::assertSame(1, $metrics->getRequestsLastMinute());
        self::assertSame(1, $metrics->getRequestsLastHour());
        self::assertSame(119, $metrics->getRemainingMinuteQuota()); // 120 - 1

        // Test reset functionality
        $rateLimitManager->resetLimits($credential);

        $metricsAfterReset = $rateLimitManager->getUsageMetrics($credential);
        self::assertSame(0, $metricsAfterReset->getRequestsLastMinute());
        self::assertSame(0, $metricsAfterReset->getRequestsLastHour());
    }
}

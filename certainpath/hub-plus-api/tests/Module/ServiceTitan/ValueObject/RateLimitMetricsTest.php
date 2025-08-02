<?php

declare(strict_types=1);

namespace App\Tests\Module\ServiceTitan\ValueObject;

use App\Module\ServiceTitan\ValueObject\RateLimitMetrics;
use PHPUnit\Framework\TestCase;

class RateLimitMetricsTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $requestsLastMinute = 45;
        $requestsLastHour = 1800;
        $remainingMinuteQuota = 75;
        $remainingHourQuota = 1800;
        $averageRequestsPerMinute = 30.5;
        $nextResetTime = 1234567890;

        $metrics = new RateLimitMetrics(
            $requestsLastMinute,
            $requestsLastHour,
            $remainingMinuteQuota,
            $remainingHourQuota,
            $averageRequestsPerMinute,
            $nextResetTime
        );

        self::assertSame($requestsLastMinute, $metrics->getRequestsLastMinute());
        self::assertSame($requestsLastHour, $metrics->getRequestsLastHour());
        self::assertSame($remainingMinuteQuota, $metrics->getRemainingMinuteQuota());
        self::assertSame($remainingHourQuota, $metrics->getRemainingHourQuota());
        self::assertSame($averageRequestsPerMinute, $metrics->getAverageRequestsPerMinute());
        self::assertSame($nextResetTime, $metrics->getNextResetTime());
    }

    public function testConstructorWithNullResetTime(): void
    {
        $metrics = new RateLimitMetrics(10, 100, 50, 2500, 15.0);

        self::assertNull($metrics->getNextResetTime());
    }

    public function testIsNearMinuteLimitWhenClose(): void
    {
        // 108 requests out of 120 total (90%)
        $metrics = new RateLimitMetrics(108, 2000, 12, 1600, 25.0, 1234567890);

        self::assertTrue($metrics->isNearMinuteLimit());
    }

    public function testIsNearMinuteLimitWhenNotClose(): void
    {
        // 50 requests out of 120 total (41.7%)
        $metrics = new RateLimitMetrics(50, 2000, 70, 1600, 25.0, 1234567890);

        self::assertFalse($metrics->isNearMinuteLimit());
    }

    public function testIsNearMinuteLimitWhenZeroLimit(): void
    {
        // Edge case: no requests and no quota
        $metrics = new RateLimitMetrics(0, 0, 0, 0, 0.0, 1234567890);

        self::assertFalse($metrics->isNearMinuteLimit());
    }

    public function testIsNearHourlyLimitWhenClose(): void
    {
        // 3240 requests out of 3600 total (90%)
        $metrics = new RateLimitMetrics(54, 3240, 66, 360, 54.0, 1234567890);

        self::assertTrue($metrics->isNearHourlyLimit());
    }

    public function testIsNearHourlyLimitWhenNotClose(): void
    {
        // 1800 requests out of 3600 total (50%)
        $metrics = new RateLimitMetrics(30, 1800, 90, 1800, 30.0, 1234567890);

        self::assertFalse($metrics->isNearHourlyLimit());
    }

    public function testGetMinuteUtilizationPercentage(): void
    {
        // 45 requests out of 120 total (37.5%)
        $metrics = new RateLimitMetrics(45, 1500, 75, 2100, 25.0, 1234567890);

        self::assertSame(37.5, $metrics->getMinuteUtilizationPercentage());
    }

    public function testGetMinuteUtilizationPercentageWithZeroLimit(): void
    {
        $metrics = new RateLimitMetrics(0, 0, 0, 0, 0.0, 1234567890);

        self::assertSame(0.0, $metrics->getMinuteUtilizationPercentage());
    }

    public function testGetHourlyUtilizationPercentage(): void
    {
        // 1800 requests out of 3600 total (50%)
        $metrics = new RateLimitMetrics(30, 1800, 90, 1800, 30.0, 1234567890);

        self::assertSame(50.0, $metrics->getHourlyUtilizationPercentage());
    }

    public function testGetHourlyUtilizationPercentageWithZeroLimit(): void
    {
        $metrics = new RateLimitMetrics(0, 0, 0, 0, 0.0, 1234567890);

        self::assertSame(0.0, $metrics->getHourlyUtilizationPercentage());
    }

    public function testGetSecondsUntilResetWithFutureTime(): void
    {
        $futureTime = time() + 300; // 5 minutes in the future
        $metrics = new RateLimitMetrics(50, 2000, 70, 1600, 25.0, $futureTime);

        $secondsUntilReset = $metrics->getSecondsUntilReset();

        // Should be approximately 300 seconds (allowing for test execution time)
        self::assertGreaterThanOrEqual(299, $secondsUntilReset);
        self::assertLessThanOrEqual(300, $secondsUntilReset);
    }

    public function testGetSecondsUntilResetWithPastTime(): void
    {
        $pastTime = time() - 100; // 100 seconds ago
        $metrics = new RateLimitMetrics(50, 2000, 70, 1600, 25.0, $pastTime);

        self::assertSame(0, $metrics->getSecondsUntilReset());
    }

    public function testGetSecondsUntilResetWithNullTime(): void
    {
        $metrics = new RateLimitMetrics(50, 2000, 70, 1600, 25.0, null);

        self::assertSame(0, $metrics->getSecondsUntilReset());
    }

    public function testIsSustainableRateWhenTrue(): void
    {
        // Average 25 requests per minute, limit is 120, so 25 <= 108 (90% of 120)
        $metrics = new RateLimitMetrics(50, 2000, 70, 1600, 25.0, 1234567890);

        self::assertTrue($metrics->isSustainableRate());
    }

    public function testIsSustainableRateWhenFalse(): void
    {
        // Average 110 requests per minute, limit is 120, so 110 > 108 (90% of 120)
        $metrics = new RateLimitMetrics(50, 2000, 70, 1600, 110.0, 1234567890);

        self::assertFalse($metrics->isSustainableRate());
    }

    public function testIsSustainableRateWithZeroLimit(): void
    {
        $metrics = new RateLimitMetrics(0, 0, 0, 0, 50.0, 1234567890);

        self::assertTrue($metrics->isSustainableRate());
    }

    public function testToArray(): void
    {
        $nextResetTime = time() + 300;
        $metrics = new RateLimitMetrics(45, 1800, 75, 1800, 30.0, $nextResetTime);

        $array = $metrics->toArray();

        $expectedKeys = [
            'requests_last_minute',
            'requests_last_hour',
            'remaining_minute_quota',
            'remaining_hour_quota',
            'average_requests_per_minute',
            'minute_utilization_percentage',
            'hourly_utilization_percentage',
            'seconds_until_reset',
            'near_minute_limit',
            'near_hourly_limit',
            'sustainable_rate',
            'next_reset_time',
        ];

        foreach ($expectedKeys as $key) {
            self::assertArrayHasKey($key, $array, "Array should contain key: $key");
        }

        self::assertSame(45, $array['requests_last_minute']);
        self::assertSame(1800, $array['requests_last_hour']);
        self::assertSame(75, $array['remaining_minute_quota']);
        self::assertSame(1800, $array['remaining_hour_quota']);
        self::assertSame(30.0, $array['average_requests_per_minute']);
        self::assertSame($nextResetTime, $array['next_reset_time']);

        self::assertIsBool($array['near_minute_limit']);
        self::assertIsBool($array['near_hourly_limit']);
        self::assertIsBool($array['sustainable_rate']);
        self::assertIsFloat($array['minute_utilization_percentage']);
        self::assertIsFloat($array['hourly_utilization_percentage']);
        self::assertIsInt($array['seconds_until_reset']);
    }

    public function testToArrayWithComplexScenario(): void
    {
        // Create a scenario where we're near limits
        // 108 requests last minute, 12 remaining = 120 total limit
        // Average 54 requests per minute is 45% of 120, so it's sustainable (54 <= 108)
        // Let's make it unsustainable by setting average to 110
        $metrics = new RateLimitMetrics(108, 3240, 12, 360, 110.0, time() + 45);

        $array = $metrics->toArray();

        self::assertTrue($array['near_minute_limit'], 'Should be near minute limit');
        self::assertTrue($array['near_hourly_limit'], 'Should be near hourly limit');
        self::assertFalse($array['sustainable_rate'], 'Rate should not be sustainable');
        self::assertSame(90.0, $array['minute_utilization_percentage']);
        self::assertSame(90.0, $array['hourly_utilization_percentage']);
        self::assertGreaterThan(40, $array['seconds_until_reset']);
        self::assertLessThanOrEqual(45, $array['seconds_until_reset']);
    }
}

<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\ValueObject;

/**
 * Rate limit metrics for ServiceTitan API usage tracking
 *
 * Provides comprehensive metrics about current API usage patterns,
 * remaining quotas, and timing information for rate limit management.
 */
readonly class RateLimitMetrics
{
    public function __construct(
        private int $requestsLastMinute,
        private int $requestsLastHour,
        private int $remainingMinuteQuota,
        private int $remainingHourQuota,
        private float $averageRequestsPerMinute,
        private ?int $nextResetTime = null
    ) {
    }

    /**
     * Get the number of requests made in the last minute
     */
    public function getRequestsLastMinute(): int
    {
        return $this->requestsLastMinute;
    }

    /**
     * Get the number of requests made in the last hour
     */
    public function getRequestsLastHour(): int
    {
        return $this->requestsLastHour;
    }

    /**
     * Get the remaining requests allowed in the current minute
     */
    public function getRemainingMinuteQuota(): int
    {
        return $this->remainingMinuteQuota;
    }

    /**
     * Get the remaining requests allowed in the current hour
     */
    public function getRemainingHourQuota(): int
    {
        return $this->remainingHourQuota;
    }

    /**
     * Get the average requests per minute over the last hour
     */
    public function getAverageRequestsPerMinute(): float
    {
        return $this->averageRequestsPerMinute;
    }

    /**
     * Get the timestamp when rate limits will reset (start of next minute)
     */
    public function getNextResetTime(): ?int
    {
        return $this->nextResetTime;
    }

    /**
     * Check if we're close to hitting the minute limit (within 10%)
     */
    public function isNearMinuteLimit(): bool
    {
        $totalMinuteLimit = $this->requestsLastMinute + $this->remainingMinuteQuota;
        if ($totalMinuteLimit === 0) {
            return false;
        }

        return ($this->requestsLastMinute / $totalMinuteLimit) >= 0.9;
    }

    /**
     * Check if we're close to hitting the hourly limit (within 10%)
     */
    public function isNearHourlyLimit(): bool
    {
        $totalHourlyLimit = $this->requestsLastHour + $this->remainingHourQuota;
        if ($totalHourlyLimit === 0) {
            return false;
        }

        return ($this->requestsLastHour / $totalHourlyLimit) >= 0.9;
    }

    /**
     * Get the utilization percentage for minute-based limits
     */
    public function getMinuteUtilizationPercentage(): float
    {
        $totalMinuteLimit = $this->requestsLastMinute + $this->remainingMinuteQuota;
        if ($totalMinuteLimit === 0) {
            return 0.0;
        }

        return ($this->requestsLastMinute / $totalMinuteLimit) * 100.0;
    }

    /**
     * Get the utilization percentage for hourly limits
     */
    public function getHourlyUtilizationPercentage(): float
    {
        $totalHourlyLimit = $this->requestsLastHour + $this->remainingHourQuota;
        if ($totalHourlyLimit === 0) {
            return 0.0;
        }

        return ($this->requestsLastHour / $totalHourlyLimit) * 100.0;
    }

    /**
     * Get seconds until the next reset
     */
    public function getSecondsUntilReset(): int
    {
        if ($this->nextResetTime === null) {
            return 0;
        }

        return max(0, $this->nextResetTime - time());
    }

    /**
     * Check if the API usage is sustainable at current rate
     */
    public function isSustainableRate(): bool
    {
        // If we're averaging more than 90% of the per-minute limit over an hour,
        // the rate is not sustainable
        $effectiveMinuteLimit = $this->requestsLastMinute + $this->remainingMinuteQuota;
        if ($effectiveMinuteLimit === 0) {
            return true;
        }

        return $this->averageRequestsPerMinute <= ($effectiveMinuteLimit * 0.9);
    }

    /**
     * Convert metrics to array for logging or API responses
     */
    public function toArray(): array
    {
        return [
            'requests_last_minute' => $this->requestsLastMinute,
            'requests_last_hour' => $this->requestsLastHour,
            'remaining_minute_quota' => $this->remainingMinuteQuota,
            'remaining_hour_quota' => $this->remainingHourQuota,
            'average_requests_per_minute' => $this->averageRequestsPerMinute,
            'minute_utilization_percentage' => $this->getMinuteUtilizationPercentage(),
            'hourly_utilization_percentage' => $this->getHourlyUtilizationPercentage(),
            'seconds_until_reset' => $this->getSecondsUntilReset(),
            'near_minute_limit' => $this->isNearMinuteLimit(),
            'near_hourly_limit' => $this->isNearHourlyLimit(),
            'sustainable_rate' => $this->isSustainableRate(),
            'next_reset_time' => $this->nextResetTime,
        ];
    }
}

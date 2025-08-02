<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Service;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\ValueObject\RateLimitMetrics;
use Psr\Log\LoggerInterface;

/**
 * ServiceTitan API Rate Limiting Manager
 *
 * Manages API request throttling, monitors usage patterns, and ensures compliance
 * with ServiceTitan's API rate limits. Prevents API limit violations that could
 * disrupt service availability.
 */
class ServiceTitanRateLimitManager
{
    private const int DEFAULT_REQUESTS_PER_MINUTE = 120;
    private const int DEFAULT_REQUESTS_PER_HOUR = 3600;
    private const int DEFAULT_BURST_LIMIT = 20;

    /** @var array<string, array<int, int>> Request counts per credential per minute */
    private array $requestCounts = [];

    /** @var array<string, array<int, int>> Hour-based request tracking */
    private array $hourlyRequestCounts = [];

    /** @var array<string, int> Burst request counts within current second */
    private array $burstCounts = [];

    /** @var array<string, int> Last cleanup timestamp per credential */
    private array $lastCleanup = [];

    /** @var array<string, int> Rate limit override per credential (if API provides specific limits) */
    private array $dynamicLimits = [];

    /** @var array<string, int> Retry-after values from 429 responses */
    private array $retryAfterValues = [];

    private int $requestsPerMinute;
    private int $requestsPerHour;
    private int $burstLimit;

    public function __construct(
        private readonly LoggerInterface $logger,
        array $config = []
    ) {
        $this->requestsPerMinute = $config['requests_per_minute'] ?? self::DEFAULT_REQUESTS_PER_MINUTE;
        $this->requestsPerHour = $config['requests_per_hour'] ?? self::DEFAULT_REQUESTS_PER_HOUR;
        $this->burstLimit = $config['burst_limit'] ?? self::DEFAULT_BURST_LIMIT;
    }

    /**
     * Check if a request can be made without violating rate limits
     */
    public function canMakeRequest(ServiceTitanCredential $credential): bool
    {
        $credentialId = $this->getCredentialKey($credential);
        $now = time();

        // Clean old entries periodically
        $this->cleanupOldEntries($credentialId, $now);

        // Check if we're in a retry-after period from a 429 response
        if (isset($this->retryAfterValues[$credentialId])) {
            if ($now < $this->retryAfterValues[$credentialId]) {
                return false;
            }
            unset($this->retryAfterValues[$credentialId]);
        }

        $minute = intdiv($now, 60);
        $hour = intdiv($now, 3600);

        // Check minute-based limits
        $minuteRequests = $this->requestCounts[$credentialId][$minute] ?? 0;
        $effectiveMinuteLimit = $this->dynamicLimits[$credentialId] ?? $this->requestsPerMinute;

        if ($minuteRequests >= $effectiveMinuteLimit) {
            return false;
        }

        // Check hourly limits
        $hourlyRequests = $this->hourlyRequestCounts[$credentialId][$hour] ?? 0;
        if ($hourlyRequests >= $this->requestsPerHour) {
            return false;
        }

        // Check burst limits (requests within the same second)
        $second = $now;
        $burstRequests = $this->burstCounts[$credentialId.'_'.$second] ?? 0;
        if ($burstRequests >= $this->burstLimit) {
            return false;
        }

        return true;
    }

    /**
     * Record a request for rate limiting tracking
     */
    public function recordRequest(ServiceTitanCredential $credential): void
    {
        $credentialId = $this->getCredentialKey($credential);
        $now = time();
        $minute = intdiv($now, 60);
        $hour = intdiv($now, 3600);

        // Record minute-based request
        if (!isset($this->requestCounts[$credentialId])) {
            $this->requestCounts[$credentialId] = [];
        }
        $this->requestCounts[$credentialId][$minute] =
            ($this->requestCounts[$credentialId][$minute] ?? 0) + 1;

        // Record hour-based request
        if (!isset($this->hourlyRequestCounts[$credentialId])) {
            $this->hourlyRequestCounts[$credentialId] = [];
        }
        $this->hourlyRequestCounts[$credentialId][$hour] =
            ($this->hourlyRequestCounts[$credentialId][$hour] ?? 0) + 1;

        // Record burst request
        $burstKey = $credentialId.'_'.$now;
        $this->burstCounts[$burstKey] = ($this->burstCounts[$burstKey] ?? 0) + 1;

        $this->logger->debug('Request recorded for rate limiting', [
            'credential_id' => $credential->getId(),
            'minute_requests' => $this->requestCounts[$credentialId][$minute],
            'hour_requests' => $this->hourlyRequestCounts[$credentialId][$hour],
            'burst_requests' => $this->burstCounts[$burstKey],
        ]);
    }

    /**
     * Get the delay in seconds until the next request can be made
     */
    public function getDelayUntilNextRequest(ServiceTitanCredential $credential): int
    {
        if ($this->canMakeRequest($credential)) {
            return 0;
        }

        $credentialId = $this->getCredentialKey($credential);
        $now = time();

        // Check retry-after from 429 response first
        if (isset($this->retryAfterValues[$credentialId])) {
            return max(0, $this->retryAfterValues[$credentialId] - $now);
        }

        $minute = intdiv($now, 60);
        $hour = intdiv($now, 3600);

        $delays = [];

        // Calculate delay for minute limit
        $minuteRequests = $this->requestCounts[$credentialId][$minute] ?? 0;
        $effectiveMinuteLimit = $this->dynamicLimits[$credentialId] ?? $this->requestsPerMinute;

        if ($minuteRequests >= $effectiveMinuteLimit) {
            $nextMinute = ($minute + 1) * 60;
            $delays[] = $nextMinute - $now;
        }

        // Calculate delay for hourly limit
        $hourlyRequests = $this->hourlyRequestCounts[$credentialId][$hour] ?? 0;
        if ($hourlyRequests >= $this->requestsPerHour) {
            $nextHour = ($hour + 1) * 3600;
            $delays[] = $nextHour - $now;
        }

        // Calculate delay for burst limit
        $burstKey = $credentialId.'_'.$now;
        $burstRequests = $this->burstCounts[$burstKey] ?? 0;
        if ($burstRequests >= $this->burstLimit) {
            $delays[] = 1; // Wait 1 second for burst limit
        }

        return empty($delays) ? 0 : max($delays);
    }

    /**
     * Handle a rate limit exceeded response (429) from the API
     */
    public function handleRateLimitExceeded(ServiceTitanCredential $credential, int $retryAfter): void
    {
        $credentialId = $this->getCredentialKey($credential);
        $now = time();

        // Store the retry-after timestamp
        $this->retryAfterValues[$credentialId] = $now + $retryAfter;

        $this->logger->warning('ServiceTitan API rate limit exceeded', [
            'credential_id' => $credential->getId(),
            'retry_after_seconds' => $retryAfter,
            'retry_after_timestamp' => $this->retryAfterValues[$credentialId],
        ]);
    }

    /**
     * Update rate limits based on API response headers
     */
    public function updateFromHeaders(ServiceTitanCredential $credential, array $headers): void
    {
        $credentialId = $this->getCredentialKey($credential);

        // Look for standard rate limit headers
        $rateLimitHeader = $headers['X-RateLimit-Limit'][0] ??
                          $headers['x-ratelimit-limit'][0] ??
                          null;

        if ($rateLimitHeader !== null && is_numeric($rateLimitHeader)) {
            $newLimit = (int) $rateLimitHeader;

            // Only update if it's different from current limit
            if (!isset($this->dynamicLimits[$credentialId]) ||
                $this->dynamicLimits[$credentialId] !== $newLimit) {

                $this->dynamicLimits[$credentialId] = $newLimit;

                $this->logger->info('Updated rate limit from API headers', [
                    'credential_id' => $credential->getId(),
                    'new_limit' => $newLimit,
                    'previous_limit' => $this->requestsPerMinute,
                ]);
            }
        }
    }

    /**
     * Get current usage metrics for a credential
     */
    public function getUsageMetrics(ServiceTitanCredential $credential): RateLimitMetrics
    {
        $credentialId = $this->getCredentialKey($credential);
        $now = time();
        $minute = intdiv($now, 60);
        $hour = intdiv($now, 3600);

        $requestsLastMinute = $this->requestCounts[$credentialId][$minute] ?? 0;
        $requestsLastHour = $this->hourlyRequestCounts[$credentialId][$hour] ?? 0;

        $effectiveMinuteLimit = $this->dynamicLimits[$credentialId] ?? $this->requestsPerMinute;
        $remainingMinuteQuota = max(0, $effectiveMinuteLimit - $requestsLastMinute);
        $remainingHourQuota = max(0, $this->requestsPerHour - $requestsLastHour);

        // Calculate average requests per minute over the last hour
        $totalRequests = 0;
        $minutesWithRequests = 0;
        $startMinute = $minute - 59; // Last 60 minutes

        for ($m = $startMinute; $m <= $minute; $m++) {
            if (isset($this->requestCounts[$credentialId][$m])) {
                $totalRequests += $this->requestCounts[$credentialId][$m];
                $minutesWithRequests++;
            }
        }

        $averageRequestsPerMinute = $minutesWithRequests > 0 ?
            $totalRequests / 60.0 : 0.0; // Divide by 60 for per-minute average

        // Next reset time (start of next minute)
        $nextResetTime = ($minute + 1) * 60;

        return new RateLimitMetrics(
            $requestsLastMinute,
            $requestsLastHour,
            $remainingMinuteQuota,
            $remainingHourQuota,
            $averageRequestsPerMinute,
            $nextResetTime
        );
    }

    /**
     * Reset rate limits for a credential (useful for testing or manual intervention)
     */
    public function resetLimits(ServiceTitanCredential $credential): void
    {
        $credentialId = $this->getCredentialKey($credential);

        // Clear all tracking data for this credential
        unset($this->requestCounts[$credentialId]);
        unset($this->hourlyRequestCounts[$credentialId]);
        unset($this->dynamicLimits[$credentialId]);
        unset($this->retryAfterValues[$credentialId]);

        // Clear burst counts for this credential
        $burstKeysToRemove = [];
        foreach ($this->burstCounts as $key => $count) {
            if (str_starts_with($key, $credentialId.'_')) {
                $burstKeysToRemove[] = $key;
            }
        }

        foreach ($burstKeysToRemove as $key) {
            unset($this->burstCounts[$key]);
        }

        $this->logger->info('Rate limits reset for credential', [
            'credential_id' => $credential->getId(),
        ]);
    }

    /**
     * Enforce rate limit by checking and waiting if necessary
     */
    public function enforceRateLimit(ServiceTitanCredential $credential): void
    {
        if (!$this->canMakeRequest($credential)) {
            $delay = $this->getDelayUntilNextRequest($credential);

            if ($delay > 0) {
                $this->logger->info('Rate limit throttling applied', [
                    'credential_id' => $credential->getId(),
                    'delay_seconds' => $delay,
                ]);

                sleep($delay);
            }
        }

        $this->recordRequest($credential);
    }

    /**
     * Get a unique key for the credential for internal tracking
     */
    private function getCredentialKey(ServiceTitanCredential $credential): string
    {
        return 'st_'.$credential->getId();
    }

    /**
     * Clean up old tracking entries to prevent memory leaks
     */
    private function cleanupOldEntries(string $credentialId, int $now): void
    {
        // Only clean up periodically (every 5 minutes)
        $lastCleanupTime = $this->lastCleanup[$credentialId] ?? 0;
        if ($now - $lastCleanupTime < 300) {
            return;
        }

        $this->lastCleanup[$credentialId] = $now;

        $currentMinute = intdiv($now, 60);
        $currentHour = intdiv($now, 3600);

        // Remove minute entries older than 2 hours (for safety)
        if (isset($this->requestCounts[$credentialId])) {
            $cutoffMinute = $currentMinute - 120;
            foreach ($this->requestCounts[$credentialId] as $minute => $count) {
                if ($minute < $cutoffMinute) {
                    unset($this->requestCounts[$credentialId][$minute]);
                }
            }
        }

        // Remove hour entries older than 24 hours
        if (isset($this->hourlyRequestCounts[$credentialId])) {
            $cutoffHour = $currentHour - 24;
            foreach ($this->hourlyRequestCounts[$credentialId] as $hour => $count) {
                if ($hour < $cutoffHour) {
                    unset($this->hourlyRequestCounts[$credentialId][$hour]);
                }
            }
        }

        // Remove burst entries older than 1 minute
        $cutoffSecond = $now - 60;
        $burstKeysToRemove = [];
        foreach ($this->burstCounts as $key => $count) {
            if (str_starts_with($key, $credentialId.'_')) {
                $second = (int) substr($key, strrpos($key, '_') + 1);
                if ($second < $cutoffSecond) {
                    $burstKeysToRemove[] = $key;
                }
            }
        }

        foreach ($burstKeysToRemove as $key) {
            unset($this->burstCounts[$key]);
        }
    }
}

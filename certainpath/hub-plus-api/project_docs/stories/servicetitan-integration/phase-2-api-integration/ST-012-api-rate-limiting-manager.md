# User Story: ST-012 - API Rate Limiting Manager

## Story Information
- **Story ID**: ST-012
- **Epic**: ServiceTitan API Integration
- **Phase**: Phase 2 - ServiceTitan API Client & Data Transformation
- **Story Points**: 4
- **Priority**: Must Have
- **Component**: Service Layer

## User Story
**As a** system administrator  
**I want** proper API rate limiting management  
**So that** ServiceTitan API limits are respected and service remains stable

## Detailed Description
This story implements a comprehensive rate limiting service that manages API request throttling, monitors usage patterns, and ensures compliance with ServiceTitan's API rate limits. The service prevents API limit violations that could disrupt service availability.

## Acceptance Criteria
- [ ] Create rate limiting service with configurable limits
- [ ] Implement request throttling with proper delays
- [ ] Add burst limit handling
- [ ] Include rate limit monitoring and metrics
- [ ] Handle rate limit exceeded responses gracefully
- [ ] Support dynamic rate limit adjustment

## Technical Implementation Notes
- **Service Location**: `src/Module/ServiceTitan/Service/ServiceTitanRateLimitManager.php`
- **Architecture Reference**: Section 13.1

### Core Service Methods
```php
class ServiceTitanRateLimitManager
{
    public function canMakeRequest(ServiceTitanCredential $credential): bool
    public function recordRequest(ServiceTitanCredential $credential): void
    public function getDelayUntilNextRequest(ServiceTitanCredential $credential): int
    public function handleRateLimitExceeded(ServiceTitanCredential $credential, int $retryAfter): void
    public function getUsageMetrics(ServiceTitanCredential $credential): RateLimitMetrics
    public function resetLimits(ServiceTitanCredential $credential): void
}
```

### Rate Limiting Strategy
- **Per-credential tracking**: Separate limits for each ServiceTitan credential
- **Time-window based**: Rolling window rate limiting (per minute/hour)
- **Burst handling**: Allow short bursts within overall limits
- **Dynamic adjustment**: Adjust limits based on API responses

### Configuration Structure
```yaml
# config/packages/servicetitan.yaml
servicetitan:
    rate_limiting:
        integration:
            requests_per_minute: 120
            requests_per_hour: 3600
            burst_limit: 20
            backoff_multiplier: 1.5
        production:
            requests_per_minute: 120
            requests_per_hour: 3600
            burst_limit: 20
            backoff_multiplier: 1.5
```

### Rate Limit Tracking
```php
class RateLimitTracker
{
    private array $requestCounts = [];
    private array $burstCounts = [];
    
    public function addRequest(string $credentialId): void
    {
        $now = time();
        $minute = intdiv($now, 60);
        
        // Track requests per minute
        $this->requestCounts[$credentialId][$minute] = 
            ($this->requestCounts[$credentialId][$minute] ?? 0) + 1;
            
        // Clean old entries
        $this->cleanOldEntries($credentialId, $minute);
    }
}
```

### Throttling Implementation
```php
public function enforceRateLimit(ServiceTitanCredential $credential): void
{
    if (!$this->canMakeRequest($credential)) {
        $delay = $this->getDelayUntilNextRequest($credential);
        
        if ($delay > 0) {
            $this->logger->info('Rate limit throttling', [
                'credential_id' => $credential->getId(),
                'delay_seconds' => $delay
            ]);
            
            sleep($delay);
        }
    }
    
    $this->recordRequest($credential);
}
```

## Definition of Done
- [ ] Rate limiting service implemented
- [ ] Configurable rate limits working
- [ ] Proper throttling behavior verified
- [ ] Rate limit exceeded handling functional
- [ ] Monitoring and metrics collection working
- [ ] Unit tests for rate limiting logic passing
- [ ] Integration tests with API client
- [ ] Dynamic limit adjustment tested
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-009**: ServiceTitan API Client Foundation

## Testing Requirements
- Unit tests for rate limiting calculations
- Unit tests for throttling delays
- Unit tests for burst limit handling
- Integration tests with API client
- Test rate limit exceeded scenarios
- Test metrics collection accuracy

### Rate Limiting Tests
```php
class ServiceTitanRateLimitManagerTest extends TestCase
{
    public function testRateLimitEnforcement(): void
    {
        $manager = new ServiceTitanRateLimitManager([
            'requests_per_minute' => 60
        ]);
        
        $credential = $this->createTestCredential();
        
        // Make 60 requests quickly
        for ($i = 0; $i < 60; $i++) {
            self::assertTrue($manager->canMakeRequest($credential));
            $manager->recordRequest($credential);
        }
        
        // 61st request should be throttled
        self::assertFalse($manager->canMakeRequest($credential));
        self::assertGreaterThan(0, $manager->getDelayUntilNextRequest($credential));
    }
}
```

### Burst Limit Tests
```php
public function testBurstLimitHandling(): void
{
    $manager = new ServiceTitanRateLimitManager([
        'requests_per_minute' => 60,
        'burst_limit' => 10
    ]);
    
    $credential = $this->createTestCredential();
    
    // Allow burst of 10 requests in quick succession
    for ($i = 0; $i < 10; $i++) {
        self::assertTrue($manager->canMakeRequest($credential));
        $manager->recordRequest($credential);
    }
    
    // 11th burst request should be throttled
    self::assertFalse($manager->canMakeRequest($credential));
}
```

### Metrics Collection
```php
class RateLimitMetrics
{
    public function __construct(
        private readonly int $requestsLastMinute,
        private readonly int $requestsLastHour,
        private readonly int $remainingMinuteQuota,
        private readonly int $remainingHourQuota,
        private readonly float $averageRequestsPerMinute,
        private readonly ?int $nextResetTime = null
    ) {}
}
```

### Integration with API Client
```php
// In ServiceTitanClient
public function makeRequest(string $method, string $endpoint, array $data = []): ApiResponse
{
    $this->rateLimitManager->enforceRateLimit($this->credential);
    
    try {
        $response = $this->httpClient->request($method, $endpoint, $data);
        
        // Check for rate limit headers in response
        if ($response->hasHeader('X-RateLimit-Remaining')) {
            $this->rateLimitManager->updateFromHeaders($this->credential, $response->getHeaders());
        }
        
        return $response;
    } catch (RateLimitExceededException $e) {
        $this->rateLimitManager->handleRateLimitExceeded($this->credential, $e->getRetryAfter());
        throw $e;
    }
}
```

## ServiceTitan Rate Limits
Based on ServiceTitan API documentation:
- **Standard Rate Limit**: 120 requests per minute per app
- **Burst Allowance**: Short bursts allowed within limits
- **Rate Limit Headers**: `X-RateLimit-Remaining`, `X-RateLimit-Reset`
- **429 Response**: Rate limit exceeded with `Retry-After` header

## Risks and Mitigation
- **Risk**: Rate limit calculations incorrect causing API failures
- **Mitigation**: Comprehensive testing with ServiceTitan Integration environment
- **Risk**: Memory usage from tracking request history
- **Mitigation**: Regular cleanup of old tracking data

## Additional Notes
This service is critical for production stability and must handle all rate limiting scenarios gracefully. The implementation should be conservative to avoid API limit violations while maximizing throughput within limits.
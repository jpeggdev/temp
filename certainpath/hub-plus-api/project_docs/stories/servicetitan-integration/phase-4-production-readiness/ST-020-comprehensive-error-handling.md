# User Story: ST-020 - Comprehensive Error Handling

## Story Information
- **Story ID**: ST-020
- **Epic**: Production Readiness & Operations
- **Phase**: Phase 4 - Testing, Monitoring & Production Deployment
- **Story Points**: 6
- **Priority**: Must Have
- **Component**: Service Layer

## User Story
**As a** system administrator  
**I want** robust error handling and recovery mechanisms  
**So that** ServiceTitan integration is reliable and self-healing

## Detailed Description
This story implements comprehensive error handling throughout the ServiceTitan integration, including retry mechanisms, circuit breaker patterns, automatic recovery, and detailed error reporting. The system must handle failures gracefully and provide self-healing capabilities for production reliability.

## Acceptance Criteria
- [ ] Implement retry service with exponential backoff
- [ ] Add circuit breaker pattern for API failures
- [ ] Include comprehensive exception hierarchy
- [ ] Add automatic token refresh on auth failures
- [ ] Implement graceful degradation for partial failures
- [ ] Include detailed error logging and alerting

## Technical Implementation Notes
- **Service Location**: `src/Module/ServiceTitan/Service/`
- **Architecture Reference**: Sections 11.1 and 11.2

### Core Error Handling Services

#### ServiceTitanRetryService
```php
class ServiceTitanRetryService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ServiceTitanMetricsService $metricsService
    ) {}
    
    public function executeWithRetry(
        callable $operation,
        ServiceTitanCredential $credential,
        RetryConfiguration $config = null
    ): mixed {
        $config ??= RetryConfiguration::default();
        $attempt = 1;
        $lastException = null;
        
        while ($attempt <= $config->getMaxAttempts()) {
            try {
                $result = $operation();
                
                if ($attempt > 1) {
                    $this->logger->info('Operation succeeded after retry', [
                        'credential_id' => $credential->getId()->toString(),
                        'attempt' => $attempt,
                        'total_attempts' => $config->getMaxAttempts()
                    ]);
                }
                
                return $result;
                
            } catch (\Exception $e) {
                $lastException = $e;
                
                if (!$this->shouldRetry($e, $attempt, $config)) {
                    break;
                }
                
                $delay = $this->calculateDelay($attempt, $config);
                
                $this->logger->warning('Operation failed, retrying', [
                    'credential_id' => $credential->getId()->toString(),
                    'attempt' => $attempt,
                    'max_attempts' => $config->getMaxAttempts(),
                    'delay_seconds' => $delay,
                    'exception' => $e->getMessage()
                ]);
                
                $this->metricsService->recordRetryAttempt($credential, $e);
                
                if ($delay > 0) {
                    sleep($delay);
                }
                
                $attempt++;
            }
        }
        
        $this->logger->error('Operation failed after all retry attempts', [
            'credential_id' => $credential->getId()->toString(),
            'total_attempts' => $config->getMaxAttempts(),
            'final_exception' => $lastException->getMessage()
        ]);
        
        throw new RetryExhaustedException(
            "Operation failed after {$config->getMaxAttempts()} attempts",
            previous: $lastException
        );
    }
    
    private function shouldRetry(\Exception $e, int $attempt, RetryConfiguration $config): bool
    {
        if ($attempt >= $config->getMaxAttempts()) {
            return false;
        }
        
        // Don't retry on certain exceptions
        if ($e instanceof InvalidCredentialsException ||
            $e instanceof CredentialValidationException ||
            $e instanceof \InvalidArgumentException) {
            return false;
        }
        
        // Always retry on network/timeout errors
        if ($e instanceof \GuzzleHttp\Exception\ConnectException ||
            $e instanceof \GuzzleHttp\Exception\RequestException ||
            $e instanceof ServiceTitanTimeoutException) {
            return true;
        }
        
        // Retry on rate limiting
        if ($e instanceof RateLimitExceededException) {
            return true;
        }
        
        // Check if it's in the retryable exceptions list
        return in_array(get_class($e), $config->getRetryableExceptions());
    }
    
    private function calculateDelay(int $attempt, RetryConfiguration $config): int
    {
        $baseDelay = $config->getBaseDelaySeconds();
        $maxDelay = $config->getMaxDelaySeconds();
        
        // Exponential backoff with jitter
        $delay = min($baseDelay * (2 ** ($attempt - 1)), $maxDelay);
        
        // Add jitter (±25%)
        $jitter = $delay * 0.25;
        $delay += random_int((int)-$jitter, (int)$jitter);
        
        return max(0, (int)$delay);
    }
}
```

#### ServiceTitanCircuitBreaker
```php
class ServiceTitanCircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';
    
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly LoggerInterface $logger,
        private readonly CircuitBreakerConfiguration $config
    ) {}
    
    public function call(callable $operation, ServiceTitanCredential $credential): mixed
    {
        $state = $this->getState($credential);
        
        if ($state === self::STATE_OPEN) {
            if ($this->shouldAttemptReset($credential)) {
                $this->setState($credential, self::STATE_HALF_OPEN);
            } else {
                throw new CircuitBreakerOpenException(
                    'Circuit breaker is open for ServiceTitan credential'
                );
            }
        }
        
        try {
            $result = $operation();
            
            if ($state === self::STATE_HALF_OPEN) {
                $this->setState($credential, self::STATE_CLOSED);
                $this->resetFailureCount($credential);
                
                $this->logger->info('Circuit breaker reset to closed state', [
                    'credential_id' => $credential->getId()->toString()
                ]);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            $this->recordFailure($credential);
            
            if ($state === self::STATE_HALF_OPEN) {
                $this->setState($credential, self::STATE_OPEN);
                $this->extendOpenPeriod($credential);
            } elseif ($this->shouldOpenCircuit($credential)) {
                $this->setState($credential, self::STATE_OPEN);
                
                $this->logger->warning('Circuit breaker opened', [
                    'credential_id' => $credential->getId()->toString(),
                    'failure_count' => $this->getFailureCount($credential),
                    'threshold' => $this->config->getFailureThreshold()
                ]);
            }
            
            throw $e;
        }
    }
    
    private function getState(ServiceTitanCredential $credential): string
    {
        $key = $this->getStateKey($credential);
        return $this->cache->get($key, self::STATE_CLOSED);
    }
    
    private function setState(ServiceTitanCredential $credential, string $state): void
    {
        $key = $this->getStateKey($credential);
        $this->cache->set($key, $state, $this->config->getStateTtl());
    }
    
    private function shouldOpenCircuit(ServiceTitanCredential $credential): bool
    {
        $failureCount = $this->getFailureCount($credential);
        return $failureCount >= $this->config->getFailureThreshold();
    }
    
    private function shouldAttemptReset(ServiceTitanCredential $credential): bool
    {
        $key = $this->getOpenTimeKey($credential);
        $openTime = $this->cache->get($key);
        
        if (!$openTime) {
            return true;
        }
        
        $resetTime = $openTime + $this->config->getResetTimeoutSeconds();
        return time() >= $resetTime;
    }
}
```

#### ServiceTitanErrorRecoveryService
```php
class ServiceTitanErrorRecoveryService
{
    public function __construct(
        private readonly ServiceTitanAuthService $authService,
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly LoggerInterface $logger
    ) {}
    
    public function handleAuthenticationError(
        ServiceTitanCredential $credential,
        InvalidCredentialsException $exception
    ): RecoveryResult {
        $this->logger->info('Attempting authentication error recovery', [
            'credential_id' => $credential->getId()->toString(),
            'error' => $exception->getMessage()
        ]);
        
        try {
            // Attempt token refresh
            if ($credential->getRefreshToken()) {
                $refreshResult = $this->authService->refreshAccessToken($credential);
                
                if ($refreshResult) {
                    $this->logger->info('Authentication recovered via token refresh', [
                        'credential_id' => $credential->getId()->toString()
                    ]);
                    
                    return RecoveryResult::success('Token refreshed successfully');
                }
            }
            
            // Try re-authentication with stored credentials
            $authResult = $this->authService->authenticateCredential($credential);
            
            if ($authResult) {
                $this->logger->info('Authentication recovered via re-authentication', [
                    'credential_id' => $credential->getId()->toString()
                ]);
                
                return RecoveryResult::success('Re-authentication successful');
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Authentication recovery failed', [
                'credential_id' => $credential->getId()->toString(),
                'recovery_error' => $e->getMessage()
            ]);
        }
        
        // Mark credential as requiring manual intervention
        $credential->setConnectionStatus('error');
        $credential->setLastConnectionAttempt(new \DateTime());
        $this->credentialRepository->save($credential);
        
        return RecoveryResult::failure('Manual credential update required');
    }
    
    public function handlePartialSyncFailure(
        ServiceTitanSyncLog $syncLog,
        array $failedRecords
    ): RecoveryResult {
        $this->logger->info('Handling partial sync failure', [
            'sync_id' => $syncLog->getId()->toString(),
            'failed_records' => count($failedRecords),
            'total_records' => $syncLog->getRecordsProcessed()
        ]);
        
        try {
            // Attempt to process failed records individually
            $recoveredCount = 0;
            
            foreach ($failedRecords as $record) {
                try {
                    $this->processFailedRecord($record, $syncLog);
                    $recoveredCount++;
                } catch (\Exception $e) {
                    $this->logger->debug('Failed to recover individual record', [
                        'record_id' => $record['id'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            if ($recoveredCount > 0) {
                $syncLog->setRecordsSuccessful($syncLog->getRecordsSuccessful() + $recoveredCount);
                $syncLog->setRecordsFailed($syncLog->getRecordsFailed() - $recoveredCount);
                
                $this->logger->info('Partial recovery successful', [
                    'sync_id' => $syncLog->getId()->toString(),
                    'recovered_records' => $recoveredCount
                ]);
                
                return RecoveryResult::partial("Recovered {$recoveredCount} records");
            }
            
        } catch (\Exception $e) {
            $this->logger->error('Partial sync recovery failed', [
                'sync_id' => $syncLog->getId()->toString(),
                'error' => $e->getMessage()
            ]);
        }
        
        return RecoveryResult::failure('Could not recover failed records');
    }
}
```

## Definition of Done
- [ ] Retry service implemented with exponential backoff
- [ ] Circuit breaker working correctly
- [ ] Exception hierarchy complete and tested
- [ ] Token refresh on auth failures functional
- [ ] Graceful degradation mechanisms working
- [ ] Comprehensive logging and alerting integrated
- [ ] Self-healing capabilities verified
- [ ] Unit tests for all error scenarios passing
- [ ] Integration tests with failure simulation
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-004**: OAuth Authentication Service
- **ST-008**: OAuth Exception Hierarchy
- **ST-014**: ServiceTitan Integration Service

## Testing Requirements
- Unit tests for retry logic with various failure scenarios
- Unit tests for circuit breaker state transitions
- Unit tests for error recovery mechanisms
- Integration tests with simulated API failures
- Test exponential backoff calculations
- Test graceful degradation behavior

### Error Handling Tests
```php
class ServiceTitanRetryServiceTest extends TestCase
{
    public function testRetryWithExponentialBackoff(): void
    {
        $retryService = new ServiceTitanRetryService($this->logger, $this->metricsService);
        $credential = $this->createTestCredential();
        
        $attemptCount = 0;
        $operation = function() use (&$attemptCount) {
            $attemptCount++;
            if ($attemptCount < 3) {
                throw new ServiceTitanApiException('Temporary failure');
            }
            return 'success';
        };
        
        $config = new RetryConfiguration(maxAttempts: 3, baseDelaySeconds: 1);
        
        $result = $retryService->executeWithRetry($operation, $credential, $config);
        
        self::assertSame('success', $result);
        self::assertSame(3, $attemptCount);
    }
    
    public function testNoRetryOnInvalidCredentials(): void
    {
        $retryService = new ServiceTitanRetryService($this->logger, $this->metricsService);
        $credential = $this->createTestCredential();
        
        $operation = function() {
            throw new InvalidCredentialsException('Invalid credentials');
        };
        
        $this->expectException(InvalidCredentialsException::class);
        
        $retryService->executeWithRetry($operation, $credential);
    }
}
```

### Circuit Breaker Tests
```php
class ServiceTitanCircuitBreakerTest extends TestCase
{
    public function testCircuitBreakerOpensAfterFailureThreshold(): void
    {
        $circuitBreaker = new ServiceTitanCircuitBreaker(
            $this->cache,
            $this->logger,
            new CircuitBreakerConfiguration(failureThreshold: 3)
        );
        
        $credential = $this->createTestCredential();
        
        $operation = function() {
            throw new ServiceTitanApiException('API failure');
        };
        
        // First 3 failures should be allowed
        for ($i = 0; $i < 3; $i++) {
            try {
                $circuitBreaker->call($operation, $credential);
            } catch (ServiceTitanApiException $e) {
                // Expected
            }
        }
        
        // 4th attempt should trigger circuit breaker
        $this->expectException(CircuitBreakerOpenException::class);
        $circuitBreaker->call($operation, $credential);
    }
}
```

## Configuration Objects
```php
class RetryConfiguration
{
    public function __construct(
        private readonly int $maxAttempts = 3,
        private readonly int $baseDelaySeconds = 1,
        private readonly int $maxDelaySeconds = 60,
        private readonly array $retryableExceptions = [
            ServiceTitanApiException::class,
            ServiceTitanTimeoutException::class,
            RateLimitExceededException::class
        ]
    ) {}
    
    public static function default(): self
    {
        return new self();
    }
    
    public static function aggressive(): self
    {
        return new self(maxAttempts: 5, baseDelaySeconds: 2);
    }
    
    public static function conservative(): self
    {
        return new self(maxAttempts: 2, baseDelaySeconds: 5);
    }
}

class RecoveryResult
{
    private function __construct(
        private readonly bool $successful,
        private readonly string $message,
        private readonly bool $partial = false
    ) {}
    
    public static function success(string $message): self
    {
        return new self(true, $message);
    }
    
    public static function failure(string $message): self
    {
        return new self(false, $message);
    }
    
    public static function partial(string $message): self
    {
        return new self(true, $message, true);
    }
}
```

## Integration with Existing Services
```php
// In ServiceTitanIntegrationService
public function synchronizeData(
    ServiceTitanCredential $credential,
    SyncConfiguration $config
): SyncResult {
    return $this->circuitBreaker->call(function() use ($credential, $config) {
        return $this->retryService->executeWithRetry(
            function() use ($credential, $config) {
                return $this->performActualSync($credential, $config);
            },
            $credential,
            RetryConfiguration::default()
        );
    }, $credential);
}
```

## Monitoring and Alerting Integration
```php
// Error metrics collection
class ServiceTitanErrorMetrics
{
    public function recordError(ServiceTitanCredential $credential, \Exception $error): void
    {
        $this->metricsCollector->increment('servicetitan.errors.total', [
            'credential_id' => $credential->getId()->toString(),
            'error_type' => get_class($error),
            'environment' => $credential->getEnvironment()
        ]);
        
        // Trigger alert for critical errors
        if ($error instanceof CircuitBreakerOpenException ||
            $error instanceof RetryExhaustedException) {
            $this->alertManager->sendAlert('servicetitan_critical_error', [
                'credential' => $credential->getId()->toString(),
                'company' => $credential->getCompany()->getName(),
                'error' => $error->getMessage()
            ]);
        }
    }
}
```

## Risks and Mitigation
- **Risk**: Retry logic causing cascading failures
- **Mitigation**: Circuit breaker pattern and intelligent retry strategies
- **Risk**: Error recovery attempts making issues worse
- **Mitigation**: Comprehensive testing and conservative recovery strategies

## Additional Notes
This comprehensive error handling system is critical for production reliability. It must handle all failure scenarios gracefully while providing clear visibility into system health and recovery attempts.
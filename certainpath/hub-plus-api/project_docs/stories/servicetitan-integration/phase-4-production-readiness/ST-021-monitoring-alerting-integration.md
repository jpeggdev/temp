# User Story: ST-021 - Monitoring and Alerting Integration

## Story Information
- **Story ID**: ST-021
- **Epic**: Production Readiness & Operations
- **Phase**: Phase 4 - Testing, Monitoring & Production Deployment
- **Story Points**: 4
- **Priority**: Should Have
- **Component**: Monitoring Layer

## User Story
**As a** system administrator  
**I want** comprehensive monitoring of ServiceTitan operations  
**So that** I can proactively manage system health and performance

## Detailed Description
This story integrates comprehensive monitoring and alerting for the ServiceTitan integration, providing visibility into system health, performance metrics, error rates, and operational status. The monitoring enables proactive management and rapid response to issues.

## Acceptance Criteria
- [ ] Integrate with existing logging infrastructure
- [ ] Add metrics for sync operations and performance
- [ ] Include alerting for failed synchronizations
- [ ] Add dashboard metrics for connection status
- [ ] Include API response time monitoring
- [ ] Add data quality monitoring and alerts

## Technical Implementation Notes
- **Pattern**: Use existing Hub Plus API monitoring patterns
- **Integration**: Extend existing monitoring infrastructure
- **Architecture Reference**: Section 14

### Core Monitoring Components

#### ServiceTitanMetricsCollector
```php
class ServiceTitanMetricsCollector
{
    public function __construct(
        private readonly MetricsInterface $metrics,
        private readonly LoggerInterface $logger
    ) {}
    
    public function recordSyncStarted(ServiceTitanCredential $credential, string $dataType): void
    {
        $this->metrics->increment('servicetitan.sync.started', [
            'environment' => $credential->getEnvironment(),
            'data_type' => $dataType,
            'company_id' => $credential->getCompany()->getId()->toString()
        ]);
    }
    
    public function recordSyncCompleted(
        ServiceTitanCredential $credential,
        ServiceTitanSyncLog $syncLog
    ): void {
        $labels = [
            'environment' => $credential->getEnvironment(),
            'data_type' => $syncLog->getDataType(),
            'status' => $syncLog->getStatus(),
            'company_id' => $credential->getCompany()->getId()->toString()
        ];
        
        $this->metrics->increment('servicetitan.sync.completed', $labels);
        
        if ($syncLog->getProcessingTimeSeconds()) {
            $this->metrics->timing(
                'servicetitan.sync.duration',
                $syncLog->getProcessingTimeSeconds(),
                $labels
            );
        }
        
        $this->metrics->gauge(
            'servicetitan.sync.records_processed',
            $syncLog->getRecordsProcessed(),
            $labels
        );
        
        $this->metrics->gauge(
            'servicetitan.sync.records_successful',
            $syncLog->getRecordsSuccessful(),
            $labels
        );
        
        $this->metrics->gauge(
            'servicetitan.sync.records_failed',
            $syncLog->getRecordsFailed(),
            $labels
        );
        
        // Calculate success rate
        if ($syncLog->getRecordsProcessed() > 0) {
            $successRate = ($syncLog->getRecordsSuccessful() / $syncLog->getRecordsProcessed()) * 100;
            $this->metrics->gauge('servicetitan.sync.success_rate', $successRate, $labels);
        }
    }
    
    public function recordApiCall(
        ServiceTitanCredential $credential,
        string $endpoint,
        int $responseTime,
        int $statusCode
    ): void {
        $labels = [
            'environment' => $credential->getEnvironment(),
            'endpoint' => $endpoint,
            'status_code' => (string) $statusCode,
            'company_id' => $credential->getCompany()->getId()->toString()
        ];
        
        $this->metrics->increment('servicetitan.api.requests', $labels);
        $this->metrics->timing('servicetitan.api.response_time', $responseTime, $labels);
        
        if ($statusCode >= 400) {
            $this->metrics->increment('servicetitan.api.errors', $labels);
        }
    }
    
    public function recordCredentialStatus(ServiceTitanCredential $credential): void
    {
        $labels = [
            'environment' => $credential->getEnvironment(),
            'status' => $credential->getConnectionStatus(),
            'company_id' => $credential->getCompany()->getId()->toString()
        ];
        
        $this->metrics->gauge('servicetitan.credentials.status', 1, $labels);
        
        // Record token expiry metrics
        if ($credential->getTokenExpiresAt()) {
            $timeToExpiry = $credential->getTokenExpiresAt()->getTimestamp() - time();
            $this->metrics->gauge('servicetitan.credentials.token_expiry_seconds', $timeToExpiry, $labels);
        }
    }
    
    public function recordDataQualityMetrics(array $validationResults, ServiceTitanCredential $credential): void
    {
        foreach ($validationResults as $dataType => $result) {
            $labels = [
                'environment' => $credential->getEnvironment(),
                'data_type' => $dataType,
                'company_id' => $credential->getCompany()->getId()->toString()
            ];
            
            $this->metrics->gauge('servicetitan.data_quality.total_records', $result['total'], $labels);
            $this->metrics->gauge('servicetitan.data_quality.valid_records', $result['valid'], $labels);
            $this->metrics->gauge('servicetitan.data_quality.invalid_records', $result['invalid'], $labels);
            
            if ($result['total'] > 0) {
                $qualityScore = ($result['valid'] / $result['total']) * 100;
                $this->metrics->gauge('servicetitan.data_quality.score', $qualityScore, $labels);
            }
        }
    }
}
```

#### ServiceTitanAlertManager
```php
class ServiceTitanAlertManager
{
    public function __construct(
        private readonly AlertManagerInterface $alertManager,
        private readonly LoggerInterface $logger
    ) {}
    
    public function checkSyncFailures(): void
    {
        $recentFailures = $this->syncLogRepository->findRecentFailures(new \DateTime('-1 hour'));
        
        foreach ($this->groupFailuresByCredential($recentFailures) as $credentialId => $failures) {
            if (count($failures) >= 3) {
                $this->sendSyncFailureAlert($credentialId, $failures);
            }
        }
    }
    
    public function checkCredentialHealth(): void
    {
        $credentials = $this->credentialRepository->findAll();
        
        foreach ($credentials as $credential) {
            // Check for expired tokens
            if ($this->isTokenExpiringSoon($credential)) {
                $this->sendTokenExpiryAlert($credential);
            }
            
            // Check for connection errors
            if ($credential->getConnectionStatus() === 'error') {
                $this->sendConnectionErrorAlert($credential);
            }
            
            // Check for stale syncs (no sync in 48 hours)
            if ($this->isSyncStale($credential)) {
                $this->sendStaleSyncAlert($credential);
            }
        }
    }
    
    public function checkDataQuality(): void
    {
        $credentials = $this->credentialRepository->findActive();
        
        foreach ($credentials as $credential) {
            $recentSyncs = $this->syncLogRepository->findRecentCompleted($credential, new \DateTime('-24 hours'));
            
            foreach ($recentSyncs as $sync) {
                if ($this->hasDataQualityIssues($sync)) {
                    $this->sendDataQualityAlert($credential, $sync);
                }
            }
        }
    }
    
    private function sendSyncFailureAlert(string $credentialId, array $failures): void
    {
        $credential = $this->credentialRepository->find($credentialId);
        
        $alert = new Alert(
            type: 'servicetitan_sync_failures',
            severity: AlertSeverity::HIGH,
            title: 'Multiple ServiceTitan Sync Failures',
            message: sprintf(
                'ServiceTitan integration for %s (%s) has failed %d times in the last hour',
                $credential->getCompany()->getName(),
                $credential->getEnvironment(),
                count($failures)
            ),
            metadata: [
                'credential_id' => $credentialId,
                'company_name' => $credential->getCompany()->getName(),
                'environment' => $credential->getEnvironment(),
                'failure_count' => count($failures),
                'latest_error' => $failures[0]->getErrorMessage()
            ]
        );
        
        $this->alertManager->send($alert);
    }
    
    private function sendTokenExpiryAlert(ServiceTitanCredential $credential): void
    {
        $alert = new Alert(
            type: 'servicetitan_token_expiry',
            severity: AlertSeverity::MEDIUM,
            title: 'ServiceTitan Token Expiring Soon',
            message: sprintf(
                'ServiceTitan token for %s (%s) expires in %s',
                $credential->getCompany()->getName(),
                $credential->getEnvironment(),
                $this->formatTimeUntilExpiry($credential->getTokenExpiresAt())
            ),
            metadata: [
                'credential_id' => $credential->getId()->toString(),
                'company_name' => $credential->getCompany()->getName(),
                'environment' => $credential->getEnvironment(),
                'expires_at' => $credential->getTokenExpiresAt()->format('c')
            ]
        );
        
        $this->alertManager->send($alert);
    }
    
    private function sendDataQualityAlert(ServiceTitanCredential $credential, ServiceTitanSyncLog $sync): void
    {
        $failureRate = ($sync->getRecordsFailed() / $sync->getRecordsProcessed()) * 100;
        
        $alert = new Alert(
            type: 'servicetitan_data_quality',
            severity: AlertSeverity::MEDIUM,
            title: 'ServiceTitan Data Quality Issues',
            message: sprintf(
                'ServiceTitan sync for %s has high failure rate: %.1f%% (%d of %d records failed)',
                $credential->getCompany()->getName(),
                $failureRate,
                $sync->getRecordsFailed(),
                $sync->getRecordsProcessed()
            ),
            metadata: [
                'credential_id' => $credential->getId()->toString(),
                'sync_id' => $sync->getId()->toString(),
                'company_name' => $credential->getCompany()->getName(),
                'failure_rate' => $failureRate,
                'failed_records' => $sync->getRecordsFailed(),
                'total_records' => $sync->getRecordsProcessed()
            ]
        );
        
        $this->alertManager->send($alert);
    }
}
```

#### ServiceTitanHealthCheck
```php
class ServiceTitanHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly ServiceTitanClient $client,
        private readonly ServiceTitanSyncLogRepository $syncLogRepository
    ) {}
    
    public function check(): HealthCheckResult
    {
        $checks = [
            'credentials' => $this->checkCredentials(),
            'api_connectivity' => $this->checkApiConnectivity(),
            'recent_syncs' => $this->checkRecentSyncs(),
            'system_resources' => $this->checkSystemResources()
        ];
        
        $overallHealth = $this->calculateOverallHealth($checks);
        
        return new HealthCheckResult(
            name: 'servicetitan_integration',
            status: $overallHealth['status'],
            details: $checks,
            metadata: [
                'last_check' => (new \DateTime())->format('c'),
                'total_credentials' => $this->credentialRepository->count([]),
                'active_credentials' => $this->credentialRepository->countActive()
            ]
        );
    }
    
    private function checkCredentials(): array
    {
        $credentials = $this->credentialRepository->findAll();
        $issues = [];
        
        foreach ($credentials as $credential) {
            if ($credential->getConnectionStatus() === 'error') {
                $issues[] = [
                    'credential_id' => $credential->getId()->toString(),
                    'company' => $credential->getCompany()->getName(),
                    'issue' => 'Connection error'
                ];
            }
            
            if ($this->isTokenExpiringSoon($credential)) {
                $issues[] = [
                    'credential_id' => $credential->getId()->toString(),
                    'company' => $credential->getCompany()->getName(),
                    'issue' => 'Token expiring soon'
                ];
            }
        }
        
        return [
            'status' => empty($issues) ? 'healthy' : 'warning',
            'total_credentials' => count($credentials),
            'issues' => $issues
        ];
    }
    
    private function checkApiConnectivity(): array
    {
        $testResults = [];
        
        // Test with a sample of active credentials
        $sampleCredentials = $this->credentialRepository->findSampleActive(3);
        
        foreach ($sampleCredentials as $credential) {
            try {
                $response = $this->client->testConnection($credential);
                $testResults[] = [
                    'credential_id' => $credential->getId()->toString(),
                    'status' => 'success',
                    'response_time' => $response->getResponseTime()
                ];
            } catch (\Exception $e) {
                $testResults[] = [
                    'credential_id' => $credential->getId()->toString(),
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $successCount = count(array_filter($testResults, fn($r) => $r['status'] === 'success'));
        $totalTests = count($testResults);
        
        return [
            'status' => $successCount === $totalTests ? 'healthy' : ($successCount > 0 ? 'degraded' : 'unhealthy'),
            'success_rate' => $totalTests > 0 ? ($successCount / $totalTests) * 100 : 0,
            'test_results' => $testResults
        ];
    }
}
```

## Definition of Done
- [ ] Logging integration complete and functional
- [ ] Metrics collection working correctly
- [ ] Alerting configured and tested
- [ ] Dashboard metrics implemented
- [ ] Response time monitoring active
- [ ] Data quality alerts functional
- [ ] Health checks implemented
- [ ] Integration tests for monitoring
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-014**: ServiceTitan Integration Service
- **ST-020**: Comprehensive Error Handling

## Testing Requirements
- Unit tests for metrics collection
- Unit tests for alert conditions
- Integration tests for monitoring data flow
- Test health check functionality
- Test alert triggering scenarios

### Monitoring Tests
```php
class ServiceTitanMetricsCollectorTest extends TestCase
{
    public function testSyncMetricsRecording(): void
    {
        $metricsCollector = new ServiceTitanMetricsCollector($this->metrics, $this->logger);
        $credential = $this->createTestCredential();
        $syncLog = $this->createTestSyncLog([
            'recordsProcessed' => 100,
            'recordsSuccessful' => 95,
            'recordsFailed' => 5,
            'processingTimeSeconds' => 120
        ]);
        
        $metricsCollector->recordSyncCompleted($credential, $syncLog);
        
        self::assertTrue($this->metrics->hasMetric('servicetitan.sync.completed'));
        self::assertTrue($this->metrics->hasMetric('servicetitan.sync.duration'));
        self::assertEquals(95, $this->metrics->getGaugeValue('servicetitan.sync.success_rate'));
    }
    
    public function testApiCallMetrics(): void
    {
        $metricsCollector = new ServiceTitanMetricsCollector($this->metrics, $this->logger);
        $credential = $this->createTestCredential();
        
        $metricsCollector->recordApiCall($credential, '/customers', 250, 200);
        
        self::assertTrue($this->metrics->hasMetric('servicetitan.api.requests'));
        self::assertTrue($this->metrics->hasMetric('servicetitan.api.response_time'));
        self::assertEquals(250, $this->metrics->getTimingValue('servicetitan.api.response_time'));
    }
}
```

### Alert Manager Tests
```php
class ServiceTitanAlertManagerTest extends TestCase
{
    public function testSyncFailureAlert(): void
    {
        $alertManager = new ServiceTitanAlertManager($this->alertManager, $this->logger);
        
        // Create multiple failed syncs for same credential
        $credential = $this->createTestCredential();
        $failures = [];
        for ($i = 0; $i < 3; $i++) {
            $failures[] = $this->createFailedSyncLog($credential);
        }
        
        $this->syncLogRepository->method('findRecentFailures')
            ->willReturn($failures);
        
        $alertManager->checkSyncFailures();
        
        self::assertTrue($this->alertManager->wasAlertSent('servicetitan_sync_failures'));
    }
}
```

## Dashboard Metrics
Key metrics exposed for operational dashboards:

### Sync Health Metrics
- Total syncs per hour/day
- Success rate percentage
- Average sync duration
- Records processed per sync
- Failed sync count and reasons

### Credential Health Metrics
- Total active credentials
- Credentials with connection errors
- Tokens expiring within 7 days
- Last successful sync per credential

### API Performance Metrics
- Average API response time
- API error rate by endpoint
- Rate limiting incidents
- Circuit breaker activations

### Data Quality Metrics
- Record validation failure rate
- Data completeness scores
- Field-level quality metrics
- Trending quality over time

## Integration with Existing Monitoring
```php
// In services.yaml
services:
    App\Module\ServiceTitan\Service\ServiceTitanMetricsCollector:
        arguments:
            $metrics: '@app.metrics.collector'
            $logger: '@monolog.logger.servicetitan'
    
    App\Module\ServiceTitan\Service\ServiceTitanAlertManager:
        arguments:
            $alertManager: '@app.alerting.manager'
            $logger: '@monolog.logger.servicetitan'
        tags:
            - { name: 'app.scheduled_task', method: 'checkSyncFailures', schedule: '*/15 * * * *' }
            - { name: 'app.scheduled_task', method: 'checkCredentialHealth', schedule: '0 */6 * * *' }
```

## Risks and Mitigation
- **Risk**: Monitoring overhead impacting performance
- **Mitigation**: Efficient metrics collection and configurable monitoring levels
- **Risk**: Alert fatigue from too many notifications
- **Mitigation**: Intelligent alert thresholds and grouping

## Additional Notes
This monitoring system provides comprehensive visibility into ServiceTitan integration health and performance. The metrics and alerts should be tuned based on operational experience to provide meaningful insights without overwhelming operations teams.
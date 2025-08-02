# User Story: ST-022 - Integration Test Suite

## Story Information
- **Story ID**: ST-022
- **Epic**: Production Readiness & Operations
- **Phase**: Phase 4 - Testing, Monitoring & Production Deployment
- **Story Points**: 4
- **Priority**: Must Have
- **Component**: Testing Layer

## User Story
**As a** quality assurance engineer  
**I want** comprehensive integration tests  
**So that** ServiceTitan integration works correctly end-to-end

## Detailed Description
This story creates a comprehensive integration test suite that validates the complete ServiceTitan integration from OAuth authentication through data synchronization. The tests ensure all components work together correctly and can handle real-world scenarios including error conditions and edge cases.

## Acceptance Criteria
- [ ] Create integration tests for complete OAuth flow
- [ ] Add tests for full data synchronization process
- [ ] Include tests for error scenarios and recovery
- [ ] Add performance tests for large datasets
- [ ] Include tests for concurrent operations
- [ ] Add data accuracy validation tests

## Technical Implementation Notes
- **Test Location**: `tests/Module/ServiceTitan/`
- **Pattern**: Use AbstractKernelTestCase for integration tests
- **Architecture Reference**: Section 12

### Integration Test Structure

#### ServiceTitanOAuthIntegrationTest
```php
class ServiceTitanOAuthIntegrationTest extends AbstractKernelTestCase
{
    private ServiceTitanAuthService $authService;
    private ServiceTitanCredentialRepository $credentialRepository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authService = $this->getService(ServiceTitanAuthService::class);
        $this->credentialRepository = $this->getRepository(ServiceTitanCredentialRepository::class);
    }
    
    public function testCompleteOAuthFlow(): void
    {
        // Create test credential with valid ServiceTitan Integration credentials
        $credential = $this->createTestCredential([
            'clientId' => $_ENV['SERVICETITAN_TEST_CLIENT_ID'],
            'clientSecret' => $_ENV['SERVICETITAN_TEST_CLIENT_SECRET'],
            'environment' => 'integration'
        ]);
        
        // Test initial authentication
        $authResult = $this->authService->authenticateCredential($credential);
        
        self::assertTrue($authResult);
        self::assertNotNull($credential->getAccessToken());
        self::assertNotNull($credential->getRefreshToken());
        self::assertNotNull($credential->getTokenExpiresAt());
        self::assertSame('active', $credential->getConnectionStatus());
        
        // Verify credential is saved with encrypted tokens
        $savedCredential = $this->credentialRepository->find($credential->getId());
        self::assertNotNull($savedCredential);
        self::assertNotSame($credential->getAccessToken(), $savedCredential->getClientSecret()); // Should be encrypted
    }
    
    public function testTokenRefreshFlow(): void
    {
        $credential = $this->createTestCredentialWithExpiredToken();
        
        $originalAccessToken = $credential->getAccessToken();
        
        $refreshResult = $this->authService->refreshAccessToken($credential);
        
        self::assertTrue($refreshResult);
        self::assertNotSame($originalAccessToken, $credential->getAccessToken());
        self::assertGreaterThan(time(), $credential->getTokenExpiresAt()->getTimestamp());
    }
    
    public function testInvalidCredentialsHandling(): void
    {
        $credential = $this->createTestCredential([
            'clientId' => 'invalid-client-id',
            'clientSecret' => 'invalid-client-secret',
            'environment' => 'integration'
        ]);
        
        $this->expectException(InvalidCredentialsException::class);
        $this->authService->authenticateCredential($credential);
        
        // Verify credential status is updated
        self::assertSame('error', $credential->getConnectionStatus());
        self::assertNotNull($credential->getLastConnectionAttempt());
    }
    
    public function testConnectionTesting(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        $connectionResult = $this->authService->testConnection($credential);
        
        self::assertTrue($connectionResult->isSuccessful());
        self::assertGreaterThan(0, $connectionResult->getResponseTime());
    }
}
```

#### ServiceTitanDataSyncIntegrationTest
```php
class ServiceTitanDataSyncIntegrationTest extends AbstractKernelTestCase
{
    private ServiceTitanIntegrationService $integrationService;
    private ServiceTitanSyncLogRepository $syncLogRepository;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->integrationService = $this->getService(ServiceTitanIntegrationService::class);
        $this->syncLogRepository = $this->getRepository(ServiceTitanSyncLogRepository::class);
    }
    
    public function testCompleteCustomerSynchronization(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        $config = new SyncConfiguration(
            dataType: 'customers',
            syncType: 'manual',
            dateRange: new DateRange(new \DateTime('-7 days'), new \DateTime()),
            batchSize: 100
        );
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        self::assertGreaterThan(0, $result->getProcessedCount());
        self::assertLessThanOrEqual($result->getProcessedCount(), $result->getSuccessfulCount() + $result->getFailedCount());
        
        // Verify sync log was created and populated
        $syncLog = $result->getSyncLog();
        self::assertSame('completed', $syncLog->getStatus());
        self::assertSame('customers', $syncLog->getDataType());
        self::assertSame('manual', $syncLog->getSyncType());
        self::assertNotNull($syncLog->getCompletedAt());
        self::assertGreaterThan(0, $syncLog->getProcessingTimeSeconds());
        
        // Verify records were actually processed through existing pipeline
        $this->assertCustomerRecordsProcessed($result->getProcessedCount());
    }
    
    public function testInvoiceSynchronization(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        $config = new SyncConfiguration(
            dataType: 'invoices',
            syncType: 'manual',
            dateRange: new DateRange(new \DateTime('-7 days'), new \DateTime()),
            batchSize: 50
        );
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        self::assertGreaterThan(0, $result->getProcessedCount());
        
        // Verify invoice records follow expected format
        $this->assertInvoiceRecordsProcessed($result->getProcessedCount());
    }
    
    public function testCombinedDataSynchronization(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        $config = new SyncConfiguration(
            dataType: 'both',
            syncType: 'manual',
            incrementalOnly: false
        );
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        self::assertGreaterThan(0, $result->getProcessedCount());
        
        // Verify both customer and invoice records were processed
        $syncLog = $result->getSyncLog();
        self::assertSame('both', $syncLog->getDataType());
        
        $this->assertBothDataTypesProcessed();
    }
    
    public function testIncrementalSynchronization(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        // First sync - full sync
        $fullSyncConfig = new SyncConfiguration(
            dataType: 'customers',
            syncType: 'manual',
            incrementalOnly: false
        );
        
        $fullResult = $this->integrationService->synchronizeData($credential, $fullSyncConfig);
        self::assertTrue($fullResult->isSuccessful());
        
        // Wait a moment to ensure timestamp difference
        sleep(2);
        
        // Second sync - incremental only
        $incrementalConfig = new SyncConfiguration(
            dataType: 'customers',
            syncType: 'manual',
            incrementalOnly: true
        );
        
        $incrementalResult = $this->integrationService->synchronizeData($credential, $incrementalConfig);
        self::assertTrue($incrementalResult->isSuccessful());
        
        // Incremental sync should process fewer or equal records
        self::assertLessThanOrEqual($fullResult->getProcessedCount(), $incrementalResult->getProcessedCount());
    }
}
```

#### ServiceTitanErrorHandlingIntegrationTest
```php
class ServiceTitanErrorHandlingIntegrationTest extends AbstractKernelTestCase
{
    public function testRetryOnTemporaryFailure(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        // Mock a temporary API failure scenario
        $this->mockServiceTitanApiWithTemporaryFailure();
        
        $config = new SyncConfiguration(
            dataType: 'customers',
            syncType: 'manual'
        );
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        // Should eventually succeed after retries
        self::assertTrue($result->isSuccessful());
        
        // Verify retry attempts were logged
        $this->assertRetryAttemptsLogged($credential);
    }
    
    public function testCircuitBreakerActivation(): void
    {
        $credential = $this->createTestCredential();
        
        // Mock repeated API failures
        $this->mockServiceTitanApiWithRepeatedFailures();
        
        $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
        
        // First few attempts should fail normally
        for ($i = 0; $i < 3; $i++) {
            try {
                $this->integrationService->synchronizeData($credential, $config);
                self::fail('Expected sync to fail');
            } catch (ServiceTitanApiException $e) {
                // Expected
            }
        }
        
        // Subsequent attempts should trigger circuit breaker
        $this->expectException(CircuitBreakerOpenException::class);
        $this->integrationService->synchronizeData($credential, $config);
    }
    
    public function testPartialSyncFailureRecovery(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        // Mock API to return some invalid records mixed with valid ones
        $this->mockServiceTitanApiWithPartialFailures();
        
        $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        // Should complete with partial success
        self::assertTrue($result->isSuccessful());
        self::assertGreaterThan(0, $result->getSuccessfulCount());
        self::assertGreaterThan(0, $result->getFailedCount());
        
        // Verify error recovery attempts were made
        $syncLog = $result->getSyncLog();
        self::assertNotNull($syncLog->getErrorDetails());
    }
}
```

#### ServiceTitanPerformanceIntegrationTest
```php
class ServiceTitanPerformanceIntegrationTest extends AbstractKernelTestCase
{
    public function testLargeDatasetProcessing(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        // Mock API to return large dataset
        $expectedRecordCount = 10000;
        $this->mockServiceTitanApiWithLargeDataset($expectedRecordCount);
        
        $config = new SyncConfiguration(
            dataType: 'customers',
            syncType: 'manual',
            batchSize: 1000
        );
        
        $startMemory = memory_get_usage();
        $startTime = microtime(true);
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        self::assertTrue($result->isSuccessful());
        self::assertEquals($expectedRecordCount, $result->getProcessedCount());
        
        // Performance assertions
        $executionTime = $endTime - $startTime;
        $memoryIncrease = $endMemory - $startMemory;
        
        self::assertLessThan(300, $executionTime); // Should complete in under 5 minutes
        self::assertLessThan(50 * 1024 * 1024, $memoryIncrease); // Memory increase under 50MB
        
        // Verify batch processing was used (should see multiple batch completion logs)
        $this->assertBatchProcessingLogged(10); // 10 batches expected
    }
    
    public function testConcurrentSyncOperations(): void
    {
        $credentials = [
            $this->createAuthenticatedTestCredential('company1'),
            $this->createAuthenticatedTestCredential('company2'),
            $this->createAuthenticatedTestCredential('company3')
        ];
        
        $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
        
        $startTime = microtime(true);
        
        // Simulate concurrent syncs (in real scenario these would be separate processes)
        $results = [];
        foreach ($credentials as $credential) {
            $results[] = $this->integrationService->synchronizeData($credential, $config);
        }
        
        $endTime = microtime(true);
        
        // All syncs should succeed
        foreach ($results as $result) {
            self::assertTrue($result->isSuccessful());
        }
        
        // Should complete faster than sequential execution
        $executionTime = $endTime - $startTime;
        self::assertLessThan(600, $executionTime); // Under 10 minutes for 3 concurrent syncs
        
        // Verify no data conflicts occurred
        $this->assertNoConcurrencyIssues($credentials);
    }
    
    public function testRateLimitingCompliance(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
        
        // Track API call timing
        $apiCallTimes = [];
        $this->mockServiceTitanApiWithCallTimingTracking($apiCallTimes);
        
        $this->integrationService->synchronizeData($credential, $config);
        
        // Verify rate limiting was respected
        $this->assertRateLimitingRespected($apiCallTimes);
    }
}
```

#### ServiceTitanDataAccuracyIntegrationTest
```php
class ServiceTitanDataAccuracyIntegrationTest extends AbstractKernelTestCase
{
    public function testCustomerDataTransformationAccuracy(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        // Use known test data from ServiceTitan Integration environment
        $expectedCustomerData = $this->getKnownTestCustomerData();
        $this->mockServiceTitanApiWithKnownData($expectedCustomerData);
        
        $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        
        // Verify transformed data matches expectations
        $processedCustomers = $this->getProcessedCustomerRecords();
        
        foreach ($expectedCustomerData as $index => $expectedCustomer) {
            $processedCustomer = $processedCustomers[$index];
            
            // Verify critical fields are correctly transformed
            self::assertEquals($expectedCustomer['id'], $processedCustomer->getMemberId());
            self::assertEquals($expectedCustomer['name'], $processedCustomer->getCompanyName());
            self::assertEquals($expectedCustomer['email'], $processedCustomer->getEmail());
            
            // Verify phone number formatting
            $expectedPhone = $this->formatPhoneNumber($expectedCustomer['phoneNumber']);
            self::assertEquals($expectedPhone, $processedCustomer->getPhone());
            
            // Verify address concatenation
            $expectedAddress = $this->buildExpectedAddress($expectedCustomer['address']);
            self::assertEquals($expectedAddress, $processedCustomer->getAddress());
        }
    }
    
    public function testInvoiceDataTransformationAccuracy(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        $expectedInvoiceData = $this->getKnownTestInvoiceData();
        $this->mockServiceTitanApiWithKnownData($expectedInvoiceData);
        
        $config = new SyncConfiguration(dataType: 'invoices', syncType: 'manual');
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        
        $processedInvoices = $this->getProcessedInvoiceRecords();
        
        foreach ($expectedInvoiceData as $index => $expectedInvoice) {
            $processedInvoice = $processedInvoices[$index];
            
            // Verify critical fields
            self::assertEquals($expectedInvoice['id'], $processedInvoice->getInvoiceId());
            self::assertEquals($expectedInvoice['customerId'], $processedInvoice->getCustomerId());
            self::assertEquals($expectedInvoice['number'], $processedInvoice->getInvoiceNumber());
            
            // Verify date transformations
            $expectedDate = new \DateTime($expectedInvoice['invoiceDate']);
            self::assertEquals($expectedDate, $processedInvoice->getInvoiceDate());
            
            // Verify amount transformations
            $expectedAmount = $this->parseDecimal($expectedInvoice['total']);
            self::assertEquals($expectedAmount, $processedInvoice->getTotalAmount());
            
            // Verify status mapping
            $expectedStatus = $this->mapInvoiceStatus($expectedInvoice['status']);
            self::assertEquals($expectedStatus, $processedInvoice->getStatus());
        }
    }
    
    public function testDataValidationIntegration(): void
    {
        $credential = $this->createAuthenticatedTestCredential();
        
        // Include both valid and invalid data in the test dataset
        $mixedTestData = array_merge(
            $this->getValidTestData(),
            $this->getInvalidTestData()
        );
        
        $this->mockServiceTitanApiWithMixedData($mixedTestData);
        
        $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
        
        $result = $this->integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        
        // Verify only valid records were processed
        $syncLog = $result->getSyncLog();
        $expectedValidCount = count($this->getValidTestData());
        $expectedInvalidCount = count($this->getInvalidTestData());
        
        self::assertEquals($expectedValidCount, $syncLog->getRecordsSuccessful());
        self::assertEquals($expectedInvalidCount, $syncLog->getRecordsFailed());
        
        // Verify error details contain validation failure information
        $errorDetails = $syncLog->getErrorDetails();
        self::assertNotNull($errorDetails);
        self::assertArrayHasKey('validation_failures', $errorDetails);
    }
}
```

## Definition of Done
- [ ] Integration test suite complete and passing
- [ ] OAuth flow tests covering all scenarios
- [ ] Full sync process tests implemented
- [ ] Error scenario tests comprehensive
- [ ] Performance tests validating requirements
- [ ] Data accuracy validation tests passing
- [ ] Concurrent operation tests implemented
- [ ] Test data setup and teardown working
- [ ] CI/CD integration configured
- [ ] Test documentation complete

## Dependencies
- **All previous stories** (ST-001 through ST-021)

## Testing Infrastructure Requirements

### Test Environment Setup
```php
// tests/Module/ServiceTitan/ServiceTitanTestCase.php
abstract class ServiceTitanTestCase extends AbstractKernelTestCase
{
    protected function createTestCredential(array $overrides = []): ServiceTitanCredential
    {
        $credential = new ServiceTitanCredential();
        $credential->setCompany($this->getTestCompany());
        $credential->setEnvironment($overrides['environment'] ?? 'integration');
        $credential->setClientId($overrides['clientId'] ?? $_ENV['SERVICETITAN_TEST_CLIENT_ID']);
        $credential->setClientSecret($overrides['clientSecret'] ?? $_ENV['SERVICETITAN_TEST_CLIENT_SECRET']);
        
        $this->entityManager->persist($credential);
        $this->entityManager->flush();
        
        return $credential;
    }
    
    protected function createAuthenticatedTestCredential(string $companyName = 'test'): ServiceTitanCredential
    {
        $credential = $this->createTestCredential();
        
        // Set up authenticated state
        $credential->setAccessToken('test-access-token-' . uniqid());
        $credential->setRefreshToken('test-refresh-token-' . uniqid());
        $credential->setTokenExpiresAt(new \DateTime('+1 hour'));
        $credential->setConnectionStatus('active');
        
        $this->entityManager->flush();
        
        return $credential;
    }
    
    protected function mockServiceTitanApiWithKnownData(array $testData): void
    {
        // Implementation for mocking ServiceTitan API responses
        // This would integrate with the HTTP client mocking system
    }
}
```

### CI/CD Integration
```yaml
# .github/workflows/servicetitan-integration-tests.yml
name: ServiceTitan Integration Tests

on:
  push:
    paths:
      - 'src/Module/ServiceTitan/**'
      - 'tests/Module/ServiceTitan/**'
  pull_request:
    paths:
      - 'src/Module/ServiceTitan/**'
      - 'tests/Module/ServiceTitan/**'

jobs:
  integration-tests:
    runs-on: ubuntu-latest
    
    services:
      postgres:
        image: postgres:13
        env:
          POSTGRES_PASSWORD: test
        options: >-
          --health-cmd pg_isready
          --health-interval 10s
          --health-timeout 5s
          --health-retries 5
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          
      - name: Install dependencies
        run: composer install --no-interaction
        
      - name: Setup test database
        run: |
          bin/console doctrine:database:create --env=test
          bin/console doctrine:migrations:migrate -n --env=test
          
      - name: Run ServiceTitan integration tests
        run: vendor/bin/phpunit tests/Module/ServiceTitan/
        env:
          SERVICETITAN_TEST_CLIENT_ID: ${{ secrets.SERVICETITAN_TEST_CLIENT_ID }}
          SERVICETITAN_TEST_CLIENT_SECRET: ${{ secrets.SERVICETITAN_TEST_CLIENT_SECRET }}
```

## Test Data Management
```php
class ServiceTitanTestDataProvider
{
    public static function getKnownTestCustomerData(): array
    {
        return [
            [
                'id' => 'TEST-CUSTOMER-001',
                'name' => 'Test Company Inc',
                'email' => 'contact@testcompany.com',
                'phoneNumber' => '+1-555-123-4567',
                'address' => [
                    'street' => '123 Test Street',
                    'city' => 'Test City',
                    'state' => 'TS',
                    'zipCode' => '12345'
                ]
            ],
            // Additional test customers...
        ];
    }
    
    public static function getKnownTestInvoiceData(): array
    {
        return [
            [
                'id' => 'TEST-INVOICE-001',
                'customerId' => 'TEST-CUSTOMER-001',
                'number' => 'INV-001',
                'invoiceDate' => '2025-07-29T10:00:00Z',
                'total' => '$1,234.56',
                'status' => 'Paid'
            ],
            // Additional test invoices...
        ];
    }
}
```

## Risks and Mitigation
- **Risk**: Tests dependent on external ServiceTitan API availability
- **Mitigation**: Comprehensive mocking for most tests, limited real API tests
- **Risk**: Test data changes affecting test reliability
- **Mitigation**: Use dedicated test tenant with controlled data

## Additional Notes
This integration test suite provides confidence that the ServiceTitan integration works correctly end-to-end. The tests should be comprehensive enough to catch regressions while being maintainable and reliable for CI/CD execution.
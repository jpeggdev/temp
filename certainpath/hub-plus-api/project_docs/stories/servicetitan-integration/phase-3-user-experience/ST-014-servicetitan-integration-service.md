# User Story: ST-014 - ServiceTitan Integration Service

## Story Information
- **Story ID**: ST-014
- **Epic**: User Experience & Management Interface
- **Phase**: Phase 3 - UI Components & Synchronization Services
- **Story Points**: 8
- **Priority**: Must Have
- **Component**: Service Layer

## User Story
**As a** system orchestrator  
**I want** a central integration service for data synchronization  
**So that** all ServiceTitan data operations are coordinated efficiently

## Detailed Description
This story creates the central orchestration service for ServiceTitan data synchronization, mirroring the existing FieldServicesUploadService pattern. The service coordinates the complete data synchronization process including authentication, extraction, transformation, validation, and batch processing.

## Acceptance Criteria
- [ ] Create `ServiceTitanIntegrationService` mirroring existing `FieldServicesUploadService`
- [ ] Implement full data synchronization (invoices and customers)
- [ ] Use existing batch processing with 2000-record batches
- [ ] Integrate with existing progress tracking infrastructure
- [ ] Include comprehensive error handling and recovery
- [ ] Support both manual and scheduled synchronization

## Technical Implementation Notes
- **Service Location**: `src/Module/ServiceTitan/Service/ServiceTitanIntegrationService.php`
- **Pattern**: Follow existing `FieldServicesUploadService` pattern exactly
- **Architecture Reference**: Section 6.1

### Core Service Methods
```php
class ServiceTitanIntegrationService
{
    public function synchronizeData(
        ServiceTitanCredential $credential,
        SyncConfiguration $config
    ): SyncResult;
    
    public function synchronizeCustomers(
        ServiceTitanCredential $credential,
        CustomerSyncOptions $options
    ): CustomerSyncResult;
    
    public function synchronizeInvoices(
        ServiceTitanCredential $credential,
        InvoiceSyncOptions $options
    ): InvoiceSyncResult;
    
    public function testCredentials(ServiceTitanCredential $credential): ConnectionTestResult;
    
    public function getLastSyncStatus(ServiceTitanCredential $credential): ?ServiceTitanSyncLog;
}
```

### Synchronization Process Flow
1. **Authentication**: Verify and refresh OAuth credentials
2. **Data Extraction**: Pull data from ServiceTitan API with pagination
3. **Data Validation**: Validate all extracted data for quality
4. **Data Transformation**: Convert to existing record formats
5. **Batch Processing**: Process in 2000-record batches like existing services
6. **Progress Tracking**: Update sync log with progress and status
7. **Error Handling**: Manage failures and partial success scenarios

### Integration with Existing Infrastructure
```php
public function synchronizeData(
    ServiceTitanCredential $credential,
    SyncConfiguration $config
): SyncResult {
    // Create sync log entry
    $syncLog = $this->createSyncLog($credential, $config);
    
    try {
        // Authenticate
        $this->authService->validateCredential($credential);
        
        // Extract data based on configuration
        $extractedData = $this->extractData($credential, $config);
        
        // Validate data quality
        $validation = $this->validationService->validateBatch(
            $extractedData, 
            $config->getDataType()
        );
        
        // Process in batches using existing infrastructure
        $batchProcessor = $this->getBatchProcessor($config->getDataType());
        $result = $batchProcessor->processBatches(
            $extractedData,
            2000, // Standard batch size
            new ProgressTracker($syncLog)
        );
        
        // Update sync log with results
        $this->updateSyncLog($syncLog, $result);
        
        return new SyncResult($result, $syncLog);
        
    } catch (\Exception $e) {
        $this->handleSyncError($syncLog, $e);
        throw $e;
    }
}
```

### Batch Processing Integration
```php
private function getBatchProcessor(string $dataType): BatchProcessorInterface
{
    return match($dataType) {
        'customers' => new ServiceTitanCustomerBatchProcessor(
            $this->memberRecordProcessor,
            $this->progressTracker
        ),
        'invoices' => new ServiceTitanInvoiceBatchProcessor(
            $this->invoiceRecordProcessor,
            $this->progressTracker
        ),
        'both' => new ServiceTitanCombinedBatchProcessor(
            $this->memberRecordProcessor,
            $this->invoiceRecordProcessor,
            $this->progressTracker
        )
    };
}
```

### Progress Tracking Integration
```php
class ServiceTitanProgressTracker extends ProgressTracker
{
    public function updateProgress(
        ServiceTitanSyncLog $syncLog,
        int $processed,
        int $successful,
        int $failed
    ): void {
        $syncLog->setRecordsProcessed($processed);
        $syncLog->setRecordsSuccessful($successful);
        $syncLog->setRecordsFailed($failed);
        $syncLog->setUpdatedAt(new \DateTime());
        
        $this->entityManager->flush();
        
        // Emit progress event for real-time updates
        $this->eventDispatcher->dispatch(new SyncProgressEvent($syncLog));
    }
}
```

## Definition of Done
- [ ] Integration service implemented following FieldServicesUploadService pattern
- [ ] Batch processing working with existing infrastructure
- [ ] Progress tracking integrated and functional
- [ ] Error handling and recovery mechanisms working
- [ ] Both manual and scheduled sync types supported
- [ ] Unit tests with real repository operations
- [ ] Integration tests with full data flow
- [ ] Service properly registered in dependency injection
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-004**: OAuth Authentication Service
- **ST-009**: ServiceTitan API Client Foundation
- **ST-010**: ServiceTitan API Data Extraction
- **ST-011**: Data Transformation Record Maps
- **ST-013**: API Response Validation Service

## Testing Requirements
- Unit tests for complete synchronization process
- Unit tests for batch processing logic
- Unit tests for error handling scenarios
- Integration tests with real ServiceTitan API
- Test progress tracking accuracy
- Test both sync types (manual/scheduled)

### Integration Test Example
```php
class ServiceTitanIntegrationServiceTest extends AbstractKernelTestCase
{
    public function testFullCustomerSynchronization(): void
    {
        $integrationService = $this->getService(ServiceTitanIntegrationService::class);
        $credential = $this->createTestCredential();
        
        $config = new SyncConfiguration(
            dataType: 'customers',
            syncType: 'manual',
            dateRange: new DateRange(new \DateTime('-30 days'), new \DateTime())
        );
        
        $result = $integrationService->synchronizeData($credential, $config);
        
        self::assertTrue($result->isSuccessful());
        self::assertGreaterThan(0, $result->getProcessedCount());
        
        // Verify sync log was created and updated
        $syncLog = $result->getSyncLog();
        self::assertSame('completed', $syncLog->getStatus());
        self::assertGreaterThan(0, $syncLog->getRecordsProcessed());
    }
}
```

### Error Handling Tests
```php
public function testSynchronizationWithInvalidCredentials(): void
{
    $integrationService = $this->getService(ServiceTitanIntegrationService::class);
    $invalidCredential = $this->createInvalidCredential();
    
    $config = new SyncConfiguration(dataType: 'customers', syncType: 'manual');
    
    $this->expectException(InvalidCredentialsException::class);
    $integrationService->synchronizeData($invalidCredential, $config);
    
    // Verify sync log shows failure
    $syncLog = $this->getLastSyncLog($invalidCredential);
    self::assertSame('failed', $syncLog->getStatus());
    self::assertNotEmpty($syncLog->getErrorMessage());
}
```

## Configuration Objects
```php
class SyncConfiguration
{
    public function __construct(
        private readonly string $dataType, // 'customers', 'invoices', 'both'
        private readonly string $syncType, // 'manual', 'scheduled'
        private readonly ?DateRange $dateRange = null,
        private readonly bool $incrementalOnly = false,
        private readonly int $batchSize = 2000
    ) {}
}

class SyncResult
{
    public function __construct(
        private readonly bool $successful,
        private readonly int $processedCount,
        private readonly int $successfulCount,
        private readonly int $failedCount,
        private readonly ServiceTitanSyncLog $syncLog,
        private readonly array $errors = []
    ) {}
}
```

## Risks and Mitigation
- **Risk**: Long-running synchronizations timing out
- **Mitigation**: Implement proper timeout handling and progress checkpoints
- **Risk**: Memory issues with large datasets
- **Mitigation**: Use existing batch processing infrastructure
- **Risk**: Partial failures leaving system in inconsistent state
- **Mitigation**: Comprehensive error handling and recovery mechanisms

## Additional Notes
This service is critical for the ServiceTitan integration and must be thoroughly tested with various data scenarios. It should handle all edge cases gracefully and provide clear status updates throughout the synchronization process.
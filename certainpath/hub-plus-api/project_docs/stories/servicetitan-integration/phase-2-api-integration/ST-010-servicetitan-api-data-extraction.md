# User Story: ST-010 - ServiceTitan API Data Extraction

## Story Information
- **Story ID**: ST-010
- **Epic**: ServiceTitan API Integration
- **Phase**: Phase 2 - ServiceTitan API Client & Data Transformation
- **Story Points**: 6
- **Priority**: Must Have
- **Component**: Client Layer

## User Story
**As a** system integrator  
**I want** to extract customer and invoice data from ServiceTitan  
**So that** I can automate the current manual report process

## Detailed Description
This story extends the ServiceTitan API client with specific methods for extracting customer and invoice data. The implementation handles pagination, large datasets, date filtering, and incremental updates to replicate the functionality of manual reports.

## Acceptance Criteria
- [ ] Implement customer data extraction with pagination
- [ ] Implement invoice data extraction with pagination
- [ ] Handle large datasets with memory efficiency
- [ ] Add configurable date range filtering
- [ ] Include data validation and error recovery
- [ ] Support incremental data updates

## Technical Implementation Notes
- **Enhancement**: Add methods to existing `ServiceTitanClient`
- **Pagination**: Implement pagination handling for large datasets
- **Memory**: Use streaming for large datasets to prevent memory issues
- **Architecture Reference**: Section 5.1

### Customer Data Extraction Methods
```php
public function getCustomers(CustomerFilters $filters): CustomerResponse
{
    // GET /customers with pagination and filtering
}

public function getCustomersBatch(CustomerFilters $filters, int $batchSize = 1000): \Generator
{
    // Yields batches of customers for memory-efficient processing
}

public function getCustomersModifiedSince(\DateTime $since): CustomerResponse
{
    // Incremental customer updates
}
```

### Invoice Data Extraction Methods
```php
public function getInvoices(InvoiceFilters $filters): InvoiceResponse
{
    // GET /invoices with pagination and filtering
}

public function getInvoicesBatch(InvoiceFilters $filters, int $batchSize = 1000): \Generator
{
    // Yields batches of invoices for memory-efficient processing
}

public function getInvoicesModifiedSince(\DateTime $since): InvoiceResponse
{
    // Incremental invoice updates
}
```

### Filter Objects
```php
class CustomerFilters
{
    public function __construct(
        private readonly ?\DateTime $modifiedSince = null,
        private readonly ?\DateTime $createdFrom = null,
        private readonly ?\DateTime $createdTo = null,
        private readonly ?string $status = null,
        private readonly ?int $limit = null
    ) {}
}

class InvoiceFilters
{
    public function __construct(
        private readonly ?\DateTime $modifiedSince = null,
        private readonly ?\DateTime $invoiceDateFrom = null,
        private readonly ?\DateTime $invoiceDateTo = null,
        private readonly ?string $status = null,
        private readonly ?int $limit = null
    ) {}
}
```

### Memory-Efficient Processing
- Use PHP generators to yield data in batches
- Stream processing for large datasets
- Configurable batch sizes (default 1000 records)
- Memory monitoring and optimization

### Pagination Handling
- Automatic pagination through all available data
- Cursor-based pagination support
- Page size optimization based on response times
- Robust handling of pagination edge cases

## Definition of Done
- [ ] Customer and invoice extraction methods implemented
- [ ] Pagination handling working correctly
- [ ] Memory-efficient processing verified
- [ ] Date range filtering functional
- [ ] Error recovery for partial failures working
- [ ] Integration tests with real API data passing
- [ ] Batch processing generators tested
- [ ] Incremental update capability verified
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-009**: ServiceTitan API Client Foundation

## Testing Requirements
- Integration tests with ServiceTitan Integration environment
- Test pagination with various dataset sizes
- Test memory efficiency with large datasets
- Test date range filtering accuracy
- Test incremental update functionality
- Test error recovery scenarios

### Integration Test Examples
```php
class ServiceTitanDataExtractionTest extends AbstractKernelTestCase
{
    public function testCustomerExtractionWithPagination(): void
    {
        $client = $this->getService(ServiceTitanClient::class);
        $filters = new CustomerFilters(createdFrom: new \DateTime('-30 days'));
        
        $customers = [];
        foreach ($client->getCustomersBatch($filters, 100) as $batch) {
            $customers = array_merge($customers, $batch);
        }
        
        self::assertGreaterThan(0, count($customers));
        self::assertInstanceOf(Customer::class, $customers[0]);
    }
    
    public function testInvoiceExtractionWithDateFilter(): void
    {
        $client = $this->getService(ServiceTitanClient::class);
        $filters = new InvoiceFilters(
            invoiceDateFrom: new \DateTime('-7 days'),
            invoiceDateTo: new \DateTime('now')
        );
        
        $response = $client->getInvoices($filters);
        
        self::assertTrue($response->isSuccessful());
        self::assertGreaterThan(0, $response->getTotalCount());
    }
}
```

### Memory Efficiency Testing
```php
public function testLargeDatasetMemoryUsage(): void
{
    $startMemory = memory_get_usage();
    $client = $this->getService(ServiceTitanClient::class);
    
    $recordCount = 0;
    foreach ($client->getInvoicesBatch(new InvoiceFilters(), 1000) as $batch) {
        $recordCount += count($batch);
        // Process batch and verify memory doesn't grow significantly
        $currentMemory = memory_get_usage();
        self::assertLessThan($startMemory * 2, $currentMemory);
    }
    
    self::assertGreaterThan(5000, $recordCount); // Test with substantial dataset
}
```

## ServiceTitan API Endpoints
- **Customers**: `/customers` with filtering parameters
- **Invoices**: `/invoices` with filtering parameters
- **Pagination**: Standard offset/limit or cursor-based pagination
- **Rate Limits**: Respect ServiceTitan's rate limiting requirements

## Risks and Mitigation
- **Risk**: Memory exhaustion with large datasets
- **Mitigation**: Generator-based streaming and batch processing
- **Risk**: API timeouts with complex queries
- **Mitigation**: Implement request timeouts and retry logic
- **Risk**: Data consistency during long-running extractions
- **Mitigation**: Use timestamp-based incremental updates

## Additional Notes
This story is critical for automating the manual report generation process. The data extraction must be equivalent to the manual reports currently generated, ensuring no data loss during the transition to automated processing.
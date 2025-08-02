# User Story: ST-011 - Data Transformation Record Maps

## Story Information
- **Story ID**: ST-011
- **Epic**: ServiceTitan API Integration
- **Phase**: Phase 2 - ServiceTitan API Client & Data Transformation
- **Story Points**: 6
- **Priority**: Must Have
- **Component**: Value Object Layer

## User Story
**As a** data processor  
**I want** ServiceTitan API responses transformed to existing record formats  
**So that** API data flows seamlessly through existing processing pipelines

## Detailed Description
This story creates record mapping classes that transform ServiceTitan API responses into the existing InvoiceRecord and MemberRecord formats used throughout the Hub Plus API. This ensures seamless integration with existing data processing pipelines without requiring changes downstream.

## Acceptance Criteria
- [ ] Create `ServiceTitanInvoiceRecordMap` extending existing `InvoiceRecordMap`
- [ ] Create `ServiceTitanMemberRecordMap` extending existing `MemberRecordMap`
- [ ] Map all ServiceTitan API fields to existing record structures
- [ ] Handle nested API response structures
- [ ] Include field validation and data type conversion
- [ ] Add support for custom field mappings

## Technical Implementation Notes
- **Value Object Location**: `src/Module/ServiceTitan/ValueObject/`
- **Pattern**: Extend existing record map classes
- **Architecture Reference**: Section 5.2

### ServiceTitanInvoiceRecordMap
```php
class ServiceTitanInvoiceRecordMap extends InvoiceRecordMap
{
    public function __construct(array $serviceTitanInvoiceData)
    {
        $mappedData = [
            'invoice_id' => $serviceTitanInvoiceData['id'],
            'customer_id' => $serviceTitanInvoiceData['customerId'],
            'invoice_number' => $serviceTitanInvoiceData['number'],
            'invoice_date' => $this->parseDate($serviceTitanInvoiceData['invoiceDate']),
            'due_date' => $this->parseDate($serviceTitanInvoiceData['dueDate']),
            'total_amount' => $this->parseDecimal($serviceTitanInvoiceData['total']),
            'status' => $this->mapInvoiceStatus($serviceTitanInvoiceData['status']),
            // ... additional field mappings
        ];
        
        parent::__construct($mappedData);
    }
}
```

### ServiceTitanMemberRecordMap
```php
class ServiceTitanMemberRecordMap extends MemberRecordMap
{
    public function __construct(array $serviceTitanCustomerData)
    {
        $mappedData = [
            'member_id' => $serviceTitanCustomerData['id'],
            'company_name' => $serviceTitanCustomerData['name'],
            'contact_name' => $this->extractContactName($serviceTitanCustomerData),
            'email' => $serviceTitanCustomerData['email'],
            'phone' => $this->formatPhoneNumber($serviceTitanCustomerData['phoneNumber']),
            'address' => $this->buildAddress($serviceTitanCustomerData['address']),
            // ... additional field mappings
        ];
        
        parent::__construct($mappedData);
    }
}
```

### Field Mapping Requirements

#### Invoice Field Mappings
| ServiceTitan Field | Hub Plus Field | Transformation |
| --- | --- | --- |
| `id` | `invoice_id` | Direct mapping |
| `customerId` | `customer_id` | Direct mapping |
| `number` | `invoice_number` | Direct mapping |
| `invoiceDate` | `invoice_date` | Date parsing |
| `total` | `total_amount` | Decimal conversion |
| `status` | `status` | Status enumeration mapping |
| `items` | `line_items` | Array transformation |

#### Customer Field Mappings
| ServiceTitan Field | Hub Plus Field | Transformation |
| --- | --- | --- |
| `id` | `member_id` | Direct mapping |
| `name` | `company_name` | Direct mapping |
| `contacts[0]` | `contact_name` | Contact extraction |
| `email` | `email` | Direct mapping |
| `phoneNumber` | `phone` | Phone formatting |
| `address` | `address` | Address concatenation |

### Data Transformation Methods
```php
private function parseDate(?string $dateString): ?\DateTime
{
    if (!$dateString) return null;
    return new \DateTime($dateString);
}

private function parseDecimal(?string $amount): ?float
{
    if (!$amount) return null;
    return (float) str_replace(['$', ','], '', $amount);
}

private function mapInvoiceStatus(string $serviceTitanStatus): string
{
    return match($serviceTitanStatus) {
        'Draft' => 'draft',
        'Pending' => 'pending',
        'Paid' => 'paid',
        'Overdue' => 'overdue',
        default => 'unknown'
    };
}
```

## Definition of Done
- [ ] Record map classes created extending existing patterns
- [ ] All required field mappings implemented
- [ ] Data type conversion working correctly
- [ ] Field validation integrated
- [ ] Unit tests for mapping accuracy
- [ ] Comparison tests with manual report data
- [ ] Custom field mapping support added
- [ ] Error handling for missing/invalid data
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-010**: ServiceTitan API Data Extraction

## Testing Requirements
- Unit tests for each field mapping transformation
- Unit tests for data type conversions
- Unit tests for error handling scenarios
- Comparison tests using real ServiceTitan data vs manual reports
- Test custom field mapping functionality

### Mapping Accuracy Tests
```php
class ServiceTitanInvoiceRecordMapTest extends TestCase
{
    public function testInvoiceMapping(): void
    {
        $serviceTitanData = [
            'id' => 'ST-12345',
            'customerId' => 'CUST-67890',
            'number' => 'INV-001',
            'invoiceDate' => '2025-07-29T10:00:00Z',
            'total' => '$1,234.56',
            'status' => 'Paid'
        ];
        
        $recordMap = new ServiceTitanInvoiceRecordMap($serviceTitanData);
        
        self::assertSame('ST-12345', $recordMap->getInvoiceId());
        self::assertSame('CUST-67890', $recordMap->getCustomerId());
        self::assertSame('INV-001', $recordMap->getInvoiceNumber());
        self::assertEquals(1234.56, $recordMap->getTotalAmount());
        self::assertSame('paid', $recordMap->getStatus());
    }
}
```

### Data Comparison Tests
```php
public function testMappingAccuracyAgainstManualReport(): void
{
    // Load sample data from manual report
    $manualReportData = $this->loadManualReportSample();
    
    // Transform equivalent ServiceTitan API data
    $serviceTitanData = $this->loadServiceTitanApiSample();
    $recordMap = new ServiceTitanInvoiceRecordMap($serviceTitanData);
    
    // Verify critical fields match
    self::assertEquals($manualReportData['total'], $recordMap->getTotalAmount());
    self::assertEquals($manualReportData['customer'], $recordMap->getCustomerId());
}
```

## Nested Data Handling
ServiceTitan API responses often contain nested structures that need flattening:

```php
private function extractContactName(array $customerData): ?string
{
    $contacts = $customerData['contacts'] ?? [];
    return $contacts[0]['name'] ?? null;
}

private function buildAddress(array $addressData): string
{
    $parts = [
        $addressData['street'] ?? '',
        $addressData['city'] ?? '',
        $addressData['state'] ?? '',
        $addressData['zipCode'] ?? ''
    ];
    
    return implode(', ', array_filter($parts));
}
```

## Risks and Mitigation
- **Risk**: ServiceTitan API structure changes breaking mappings
- **Mitigation**: Comprehensive unit tests and validation of all mappings
- **Risk**: Data loss during transformation
- **Mitigation**: Comparison testing with manual report data
- **Risk**: Performance impact from complex transformations
- **Mitigation**: Optimize transformation logic and measure performance

## Additional Notes
These record maps are critical for maintaining compatibility with existing Hub Plus API data processing. All transformations must be thoroughly tested to ensure no data loss or corruption during the migration from manual to automated processing.
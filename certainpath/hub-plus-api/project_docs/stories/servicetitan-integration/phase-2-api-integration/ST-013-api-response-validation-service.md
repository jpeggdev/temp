# User Story: ST-013 - API Response Validation Service

## Story Information
- **Story ID**: ST-013
- **Epic**: ServiceTitan API Integration
- **Phase**: Phase 2 - ServiceTitan API Client & Data Transformation
- **Story Points**: 4
- **Priority**: Must Have
- **Component**: Service Layer

## User Story
**As a** data quality manager  
**I want** comprehensive validation of ServiceTitan API responses  
**So that** only valid data enters the processing pipeline

## Detailed Description
This story creates a comprehensive validation service for ServiceTitan API responses, ensuring data quality and preventing invalid or corrupted data from entering the Hub Plus API processing pipeline. The service validates structure, data types, business rules, and data completeness.

## Acceptance Criteria
- [ ] Create validation service for API response structure
- [ ] Implement data type and format validation
- [ ] Add business rule validation (required fields, data ranges)
- [ ] Include data quality checks against existing standards
- [ ] Handle validation failures with appropriate logging
- [ ] Support configurable validation rules

## Technical Implementation Notes
- **Service Location**: `src/Module/ServiceTitan/Service/ServiceTitanDataValidationService.php`
- **Pattern**: Use existing validation patterns from Hub Plus API
- **Architecture Reference**: Section 17.3

### Core Validation Methods
```php
class ServiceTitanDataValidationService
{
    public function validateCustomerResponse(array $responseData): ValidationResult
    public function validateInvoiceResponse(array $responseData): ValidationResult
    public function validateCustomer(array $customerData): ValidationResult
    public function validateInvoice(array $invoiceData): ValidationResult
    public function validateBatch(array $items, string $type): BatchValidationResult
}
```

### Validation Categories

#### Structure Validation
- Required field presence validation
- Data type validation (string, integer, date, etc.)
- Array structure validation for nested data
- Enum value validation for status fields

#### Business Rule Validation
```php
class CustomerValidationRules
{
    public function validateCustomer(array $customerData): array
    {
        $errors = [];
        
        // Required fields
        if (empty($customerData['id'])) {
            $errors[] = 'Customer ID is required';
        }
        
        if (empty($customerData['name'])) {
            $errors[] = 'Customer name is required';
        }
        
        // Email format validation
        if (!empty($customerData['email']) && !filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Phone number format validation
        if (!empty($customerData['phoneNumber']) && !$this->isValidPhoneNumber($customerData['phoneNumber'])) {
            $errors[] = 'Invalid phone number format';
        }
        
        return $errors;
    }
}
```

#### Data Quality Validation
```php
class InvoiceValidationRules
{
    public function validateInvoice(array $invoiceData): array
    {
        $errors = [];
        
        // Required fields
        if (empty($invoiceData['id'])) {
            $errors[] = 'Invoice ID is required';
        }
        
        if (empty($invoiceData['customerId'])) {
            $errors[] = 'Customer ID is required';
        }
        
        // Amount validation
        if (isset($invoiceData['total'])) {
            $amount = $this->parseAmount($invoiceData['total']);
            if ($amount < 0) {
                $errors[] = 'Invoice total cannot be negative';
            }
        }
        
        // Date validation
        if (!empty($invoiceData['invoiceDate'])) {
            if (!$this->isValidDate($invoiceData['invoiceDate'])) {
                $errors[] = 'Invalid invoice date format';
            }
        }
        
        // Status validation
        if (!empty($invoiceData['status'])) {
            if (!in_array($invoiceData['status'], ['Draft', 'Pending', 'Paid', 'Overdue'])) {
                $errors[] = 'Invalid invoice status';
            }
        }
        
        return $errors;
    }
}
```

### Validation Result Objects
```php
class ValidationResult
{
    public function __construct(
        private readonly bool $isValid,
        private readonly array $errors = [],
        private readonly array $warnings = []
    ) {}
    
    public function isValid(): bool { return $this->isValid; }
    public function getErrors(): array { return $this->errors; }
    public function getWarnings(): array { return $this->warnings; }
}

class BatchValidationResult
{
    public function __construct(
        private readonly int $totalItems,
        private readonly int $validItems,
        private readonly int $invalidItems,
        private readonly array $itemErrors = []
    ) {}
}
```

### Configurable Validation Rules
```yaml
# config/packages/servicetitan.yaml
servicetitan:
    validation:
        customers:
            required_fields: ['id', 'name']
            email_validation: true
            phone_validation: true
        invoices:
            required_fields: ['id', 'customerId', 'total']
            allow_negative_amounts: false
            date_format: 'Y-m-d\TH:i:s'
```

## Definition of Done
- [ ] Validation service implemented with all rule categories
- [ ] Comprehensive validation rules for customers and invoices
- [ ] Data quality checks working correctly
- [ ] Proper error handling and logging
- [ ] Unit tests for all validation scenarios
- [ ] Integration with data transformation pipeline
- [ ] Configurable validation rules
- [ ] Performance optimization for large datasets
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-010**: ServiceTitan API Data Extraction
- **ST-011**: Data Transformation Record Maps

## Testing Requirements
- Unit tests for each validation rule
- Unit tests for validation result objects
- Test validation with various invalid data scenarios
- Test batch validation performance
- Integration tests with API client and transformation

### Validation Test Examples
```php
class ServiceTitanDataValidationServiceTest extends TestCase
{
    public function testCustomerValidationWithValidData(): void
    {
        $validationService = new ServiceTitanDataValidationService();
        
        $customerData = [
            'id' => 'CUST-12345',
            'name' => 'Test Company',
            'email' => 'contact@testcompany.com',
            'phoneNumber' => '+1-555-123-4567'
        ];
        
        $result = $validationService->validateCustomer($customerData);
        
        self::assertTrue($result->isValid());
        self::assertEmpty($result->getErrors());
    }
    
    public function testCustomerValidationWithMissingRequiredFields(): void
    {
        $validationService = new ServiceTitanDataValidationService();
        
        $customerData = [
            'email' => 'contact@testcompany.com'
            // Missing required 'id' and 'name'
        ];
        
        $result = $validationService->validateCustomer($customerData);
        
        self::assertFalse($result->isValid());
        self::assertContains('Customer ID is required', $result->getErrors());
        self::assertContains('Customer name is required', $result->getErrors());
    }
}
```

### Integration with Processing Pipeline
```php
// In ServiceTitanIntegrationService
public function processCustomers(array $customers): ProcessingResult
{
    $batchValidation = $this->validationService->validateBatch($customers, 'customer');
    
    if ($batchValidation->getInvalidItems() > 0) {
        $this->logger->warning('Invalid customers found', [
            'total' => $batchValidation->getTotalItems(),
            'invalid' => $batchValidation->getInvalidItems(),
            'errors' => $batchValidation->getItemErrors()
        ]);
    }
    
    // Process only valid customers
    $validCustomers = array_filter($customers, function($customer, $index) use ($batchValidation) {
        return !isset($batchValidation->getItemErrors()[$index]);
    }, ARRAY_FILTER_USE_BOTH);
    
    return $this->processValidCustomers($validCustomers);
}
```

## Data Quality Standards
- **Completeness**: All required fields present
- **Accuracy**: Data formats and types correct
- **Consistency**: Data matches expected patterns
- **Validity**: Business rules satisfied
- **Uniqueness**: No duplicate records in batch

## Risks and Mitigation
- **Risk**: Overly strict validation blocking valid data
- **Mitigation**: Configurable validation rules and warning levels
- **Risk**: Performance impact on large datasets
- **Mitigation**: Optimize validation logic and consider batch processing

## Additional Notes
This validation service is critical for maintaining data quality and preventing issues downstream. The validation rules should be comprehensive but configurable to handle variations in ServiceTitan data formats across different clients.
# User Story: ST-017 - Request/Response DTOs

## Story Information
- **Story ID**: ST-017
- **Epic**: User Experience & Management Interface
- **Phase**: Phase 3 - UI Components & Synchronization Services
- **Story Points**: 4
- **Priority**: Must Have
- **Component**: DTO Layer

## User Story
**As a** API consumer  
**I want** well-defined request and response structures  
**So that** I can reliably interact with ServiceTitan management endpoints

## Detailed Description
This story creates comprehensive Data Transfer Objects (DTOs) for all ServiceTitan API endpoints, providing validation, documentation, and security-conscious data handling. The DTOs ensure consistent API contracts and proper data validation.

## Acceptance Criteria
- [ ] Create request DTOs for credential management
- [ ] Create response DTOs with proper data formatting
- [ ] Include validation attributes on request DTOs
- [ ] Add security-conscious response DTOs (masked credentials)
- [ ] Implement sync operation request/response DTOs
- [ ] Include comprehensive documentation

## Technical Implementation Notes
- **DTO Location**: `src/Module/ServiceTitan/Feature/*/DTO/`
- **Pattern**: Follow existing Hub Plus API DTO patterns
- **Architecture Reference**: Section 7.3

### Credential Management DTOs

#### CreateServiceTitanCredentialRequest
```php
class CreateServiceTitanCredentialRequest
{
    #[Assert\NotBlank(message: 'Client ID is required')]
    #[Assert\Length(min: 10, max: 100, minMessage: 'Client ID must be at least 10 characters')]
    public string $clientId;
    
    #[Assert\NotBlank(message: 'Client Secret is required')]
    #[Assert\Length(min: 20, max: 200, minMessage: 'Client Secret must be at least 20 characters')]
    public string $clientSecret;
    
    #[Assert\NotBlank(message: 'Environment is required')]
    #[Assert\Choice(
        choices: ['integration', 'production'],
        message: 'Environment must be either integration or production'
    )]
    public string $environment;
    
    public function toOAuthCredentials(): OAuthCredentials
    {
        return new OAuthCredentials(
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            environment: $this->environment
        );
    }
}
```

#### UpdateServiceTitanCredentialRequest
```php
class UpdateServiceTitanCredentialRequest
{
    #[Assert\Length(min: 10, max: 100)]
    public ?string $clientId = null;
    
    #[Assert\Length(min: 20, max: 200)]
    public ?string $clientSecret = null;
    
    #[Assert\Choice(choices: ['integration', 'production'])]
    public ?string $environment = null;
    
    public function hasChanges(): bool
    {
        return $this->clientId !== null || 
               $this->clientSecret !== null || 
               $this->environment !== null;
    }
}
```

#### ServiceTitanCredentialResponse
```php
class ServiceTitanCredentialResponse
{
    public readonly string $id;
    public readonly string $environment;
    public readonly string $clientId;
    public readonly string $clientSecret;
    public readonly string $connectionStatus;
    public readonly ?string $lastConnectionAttempt;
    public readonly string $createdAt;
    public readonly string $updatedAt;
    
    public function __construct(ServiceTitanCredential $credential)
    {
        $this->id = $credential->getId()->toString();
        $this->environment = $credential->getEnvironment();
        $this->clientId = $this->maskCredential($credential->getClientId());
        $this->clientSecret = $this->maskCredential($credential->getClientSecret());
        $this->connectionStatus = $credential->getConnectionStatus();
        $this->lastConnectionAttempt = $credential->getLastConnectionAttempt()?->format('c');
        $this->createdAt = $credential->getCreatedAt()->format('c');
        $this->updatedAt = $credential->getUpdatedAt()->format('c');
    }
    
    private function maskCredential(string $credential): string
    {
        $length = strlen($credential);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }
        
        return substr($credential, 0, 4) . 
               str_repeat('*', $length - 8) . 
               substr($credential, -4);
    }
}
```

### Synchronization DTOs

#### TriggerSyncRequest
```php
class TriggerSyncRequest
{
    #[Assert\NotBlank(message: 'Data type is required')]
    #[Assert\Choice(
        choices: ['customers', 'invoices', 'both'],
        message: 'Data type must be customers, invoices, or both'
    )]
    public string $dataType;
    
    #[Assert\Valid]
    public ?DateRangeDto $dateRange = null;
    
    public bool $incrementalOnly = false;
    
    #[Assert\Range(min: 100, max: 5000, notInRangeMessage: 'Batch size must be between 100 and 5000')]
    public int $batchSize = 2000;
    
    public function toSyncConfiguration(): SyncConfiguration
    {
        return new SyncConfiguration(
            dataType: $this->dataType,
            syncType: 'manual',
            dateRange: $this->dateRange?->toDateRange(),
            incrementalOnly: $this->incrementalOnly,
            batchSize: $this->batchSize
        );
    }
}
```

#### DateRangeDto
```php
class DateRangeDto
{
    #[Assert\NotBlank(message: 'From date is required')]
    #[Assert\DateTime(format: 'Y-m-d', message: 'From date must be in YYYY-MM-DD format')]
    public string $from;
    
    #[Assert\NotBlank(message: 'To date is required')]
    #[Assert\DateTime(format: 'Y-m-d', message: 'To date must be in YYYY-MM-DD format')]
    public string $to;
    
    #[Assert\Expression(
        "this.getFromDate() <= this.getToDate()",
        message: 'From date must be before or equal to to date'
    )]
    public function toDateRange(): DateRange
    {
        return new DateRange(
            new \DateTime($this->from),
            new \DateTime($this->to)
        );
    }
    
    public function getFromDate(): \DateTime
    {
        return new \DateTime($this->from);
    }
    
    public function getToDate(): \DateTime
    {
        return new \DateTime($this->to);
    }
}
```

#### SyncJobResponse
```php
class SyncJobResponse
{
    public readonly string $syncJobId;
    public readonly string $status;
    public readonly string $dataType;
    public readonly string $scheduledAt;
    public readonly ?array $estimatedCompletion;
    
    public function __construct(SyncJob $syncJob)
    {
        $this->syncJobId = $syncJob->getId()->toString();
        $this->status = $syncJob->getStatus();
        $this->dataType = $syncJob->getDataType();
        $this->scheduledAt = $syncJob->getScheduledAt()->format('c');
        $this->estimatedCompletion = $syncJob->getEstimatedCompletion() ? [
            'time' => $syncJob->getEstimatedCompletion()->format('c'),
            'confidence' => $syncJob->getEstimationConfidence()
        ] : null;
    }
}
```

#### SyncHistoryResponse
```php
class SyncHistoryResponse
{
    public readonly string $id;
    public readonly string $syncType;
    public readonly string $dataType;
    public readonly string $status;
    public readonly string $startedAt;
    public readonly ?string $completedAt;
    public readonly int $recordsProcessed;
    public readonly int $recordsSuccessful;
    public readonly int $recordsFailed;
    public readonly ?int $processingTimeSeconds;
    public readonly ?string $errorMessage;
    
    public function __construct(ServiceTitanSyncLog $syncLog)
    {
        $this->id = $syncLog->getId()->toString();
        $this->syncType = $syncLog->getSyncType();
        $this->dataType = $syncLog->getDataType();
        $this->status = $syncLog->getStatus();
        $this->startedAt = $syncLog->getStartedAt()->format('c');
        $this->completedAt = $syncLog->getCompletedAt()?->format('c');
        $this->recordsProcessed = $syncLog->getRecordsProcessed();
        $this->recordsSuccessful = $syncLog->getRecordsSuccessful();
        $this->recordsFailed = $syncLog->getRecordsFailed();
        $this->processingTimeSeconds = $syncLog->getProcessingTimeSeconds();
        $this->errorMessage = $syncLog->getErrorMessage();
    }
}
```

### Dashboard DTOs

#### ServiceTitanDashboardResponse
```php
class ServiceTitanDashboardResponse
{
    public readonly array $credentials;
    public readonly array $recentSyncs;
    public readonly array $metrics;
    public readonly array $alerts;
    
    public function __construct(ServiceTitanDashboardData $dashboardData)
    {
        $this->credentials = array_map(
            fn($credential) => new ServiceTitanCredentialSummaryResponse($credential),
            $dashboardData->getCredentials()
        );
        
        $this->recentSyncs = array_map(
            fn($sync) => new SyncHistoryResponse($sync),
            $dashboardData->getRecentSyncs()
        );
        
        $this->metrics = [
            'totalCredentials' => $dashboardData->getTotalCredentials(),
            'activeConnections' => $dashboardData->getActiveConnections(),
            'successfulSyncsToday' => $dashboardData->getSuccessfulSyncsToday(),
            'failedSyncsToday' => $dashboardData->getFailedSyncsToday(),
            'averageSyncTime' => $dashboardData->getAverageSyncTime()
        ];
        
        $this->alerts = array_map(
            fn($alert) => new ServiceTitanAlertResponse($alert),
            $dashboardData->getAlerts()
        );
    }
}
```

## Definition of Done
- [ ] All required DTOs created with validation
- [ ] Validation attributes working correctly
- [ ] Security-conscious responses implemented
- [ ] Proper documentation added
- [ ] Unit tests for DTO validation passing
- [ ] Integration with controllers tested
- [ ] Error message clarity verified
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-015**: Credential Management Controllers
- **ST-016**: Data Synchronization Controllers

## Testing Requirements
- Unit tests for all DTO validation rules
- Unit tests for data transformation methods
- Unit tests for credential masking
- Test invalid data scenarios
- Test DTO serialization/deserialization

### DTO Validation Tests
```php
class CreateServiceTitanCredentialRequestTest extends TestCase
{
    public function testValidCredentialRequest(): void
    {
        $request = new CreateServiceTitanCredentialRequest();
        $request->clientId = 'valid-client-id-123';
        $request->clientSecret = 'valid-client-secret-456789';
        $request->environment = 'integration';
        
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
            
        $violations = $validator->validate($request);
        
        self::assertCount(0, $violations);
    }
    
    public function testInvalidClientId(): void
    {
        $request = new CreateServiceTitanCredentialRequest();
        $request->clientId = 'short'; // Too short
        $request->clientSecret = 'valid-client-secret-456789';
        $request->environment = 'integration';
        
        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
            
        $violations = $validator->validate($request);
        
        self::assertGreaterThan(0, $violations->count());
        self::assertStringContains('must be at least 10 characters', (string) $violations);
    }
}
```

### Security Masking Tests
```php
class ServiceTitanCredentialResponseTest extends TestCase
{
    public function testCredentialMasking(): void
    {
        $credential = $this->createMockCredential([
            'clientId' => '1234567890abcdef',
            'clientSecret' => 'secret123456789secret'
        ]);
        
        $response = new ServiceTitanCredentialResponse($credential);
        
        self::assertSame('1234********cdef', $response->clientId);
        self::assertSame('secr***********cret', $response->clientSecret);
    }
    
    public function testShortCredentialMasking(): void
    {
        $credential = $this->createMockCredential([
            'clientId' => 'short',
            'clientSecret' => 'secret'
        ]);
        
        $response = new ServiceTitanCredentialResponse($credential);
        
        self::assertSame('*****', $response->clientId);
        self::assertSame('******', $response->clientSecret);
    }
}
```

## Validation Error Handling
```php
// Custom validation groups for different scenarios
class CreateServiceTitanCredentialRequest
{
    #[Assert\NotBlank(groups: ['create'])]
    #[Assert\Length(min: 10, max: 100, groups: ['create', 'update'])]
    public string $clientId;
    
    // Different validation for testing vs production credentials
    #[Assert\When(
        expression: "this.environment == 'production'",
        constraints: [
            new Assert\Regex(
                pattern: '/^prod_[a-zA-Z0-9]{32}$/',
                message: 'Production client ID must start with "prod_" and be 36 characters total'
            )
        ]
    )]
    public string $clientId;
}
```

## Risks and Mitigation
- **Risk**: Sensitive data exposure in DTOs
- **Mitigation**: Comprehensive credential masking in all response DTOs
- **Risk**: Validation rules too strict or too lenient
- **Mitigation**: Thorough testing with real ServiceTitan credential formats

## Additional Notes
These DTOs form the API contract for the ServiceTitan integration and must be thoroughly tested for both validation accuracy and security. The credential masking is critical for preventing accidental exposure of sensitive authentication data.
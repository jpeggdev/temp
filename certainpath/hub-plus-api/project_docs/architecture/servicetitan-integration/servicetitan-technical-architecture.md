# ServiceTitan Integration - Technical Architecture

## Document Information
- **Project**: ServiceTitan API Integration for Automated Data Extraction
- **Feature Module**: `App\Module\ServiceTitan` (first-class top-level feature)
- **Documentation Folder**: `servicetitan-integration`
- **Date**: 2025-07-29
- **Status**: Technical Architecture Design Phase
- **Priority**: High
- **Based on Requirements**: `servicetitan-integration-requirements.md`

---

## 1. Executive Summary

### Architecture Goals
This technical architecture implements automated ServiceTitan API integration by leveraging **80% of existing Hub Plus API infrastructure** while introducing ServiceTitan-specific components for OAuth authentication and API data extraction.

### Key Architectural Principles
1. **Maximum Reuse**: Leverage existing `InvoiceRecord`/`MemberRecord` entities and `IngestRepository` methods
2. **Adapter Pattern**: ServiceTitan API responses transform into existing record structures
3. **Modular Design**: Self-contained `App\Module\ServiceTitan` with clear interfaces
4. **OAuth Complexity Management**: Secure multi-tenant credential management with environment support
5. **Seamless Integration**: API-sourced data indistinguishable from file-sourced data in downstream processing

---

## 2. Module Architecture Overview

### 2.1 Module Structure
Following established Hub Plus API patterns with hierarchical organization:

```
src/Module/ServiceTitan/
├── Feature/
│   ├── OAuthManagement/
│   │   ├── Controller/
│   │   ├── DTO/
│   │   ├── Exception/
│   │   ├── Service/
│   │   └── Voter/
│   ├── DataSynchronization/
│   │   ├── Controller/
│   │   ├── DTO/
│   │   ├── Exception/
│   │   ├── Service/
│   │   └── Command/
│   └── CredentialManagement/
│       ├── Controller/
│       ├── DTO/
│       ├── Exception/
│       ├── Service/
│       └── Voter/
├── Entity/
│   ├── ServiceTitanCredential.php
│   └── ServiceTitanSyncLog.php
├── Repository/
│   ├── ServiceTitanCredentialRepository.php
│   └── ServiceTitanSyncLogRepository.php
├── Client/
│   └── ServiceTitanClient.php
├── Service/
│   ├── ServiceTitanIntegrationService.php
│   └── ServiceTitanAuthService.php
└── ValueObject/
    ├── ServiceTitanInvoiceRecordMap.php
    ├── ServiceTitanMemberRecordMap.php
    └── OAuthCredentials.php
```

### 2.2 Core Components

#### New Components (20% of architecture)
- **ServiceTitanClient**: API communication layer extending `DomainClient`
- **OAuth Management**: Multi-tenant credential storage and token management
- **Data Transformation**: ServiceTitan-specific field mapping to existing records
- **Synchronization Services**: Scheduled data extraction and processing
- **UI Components**: Credential management and sync monitoring interfaces

#### Reused Components (80% of architecture)
- **InvoiceRecord/MemberRecord**: Existing data structures (full reuse)
- **IngestRepository**: Database insertion methods (full reuse)
- **CompanyDataImportJobRepository**: Progress tracking (full reuse)
- **UnificationCompanyProcessingDispatchService**: Downstream processing (full reuse)
- **Field Traits**: CoreFieldsTrait, InvoiceFieldsTrait, MemberFieldsTrait (full reuse)

---

## 3. Data Flow Architecture

### 3.1 High-Level Data Pipeline

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   ServiceTitan  │───▶│ ServiceTitanClient│───▶│ Data Processor  │───▶│ IngestRepository│
│      API        │    │   (OAuth Auth)    │    │ (Record Mapping)│    │  (Batch Insert) │
└─────────────────┘    └──────────────────┘    └─────────────────┘    └─────────────────┘
                                                         │
                                                         ▼
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ Downstream      │◀───│  UnificationCo-  │◀───│ InvoiceRecord/  │◀───│ invoices_stream/│
│ Processing      │    │  mpanyProcessing  │    │ MemberRecord    │    │ members_stream  │
│ (Existing)      │    │ DispatchService   │    │ (Existing)      │    │ (Existing)      │
└─────────────────┘    └──────────────────┘    └─────────────────┘    └─────────────────┘
```

### 3.2 Detailed Processing Flow

1. **OAuth Authentication**: Multi-tenant credential management with automatic token refresh
2. **API Data Extraction**: Paginated data retrieval with rate limiting and error handling
3. **Data Transformation**: ServiceTitan API responses → InvoiceRecord/MemberRecord objects
4. **Batch Processing**: 2000-record batches using existing `IngestRepository->insertInvoiceRecords()`
5. **Progress Tracking**: Real-time progress updates via `CompanyDataImportJobRepository`
6. **Downstream Integration**: Seamless handoff to existing processing pipeline

---

## 4. OAuth Authentication Architecture

### 4.1 Multi-Tenant OAuth Complexity

**ServiceTitan OAuth Requirements**:
- Application registration in ServiceTitan Developer Portal
- Per-tenant approval in "Integrations > API Application Access"
- Environment-specific credentials (Integration/Production)
- Secure credential storage with encryption

### 4.2 OAuth Entity Design

#### ServiceTitanCredential Entity
```php
class ServiceTitanCredential
{
    private ?int $id = null;
    private Company $company;                    // Multi-tenant association
    private string $tenantId;                   // ServiceTitan Tenant ID
    private string $clientId;                   // Encrypted
    private string $clientSecret;               // Encrypted
    private string $environment;                // 'integration' | 'production'
    private ?string $accessToken = null;        // Encrypted, auto-refreshed
    private ?string $refreshToken = null;       // Encrypted
    private ?\DateTimeImmutable $tokenExpiresAt = null;
    private string $connectionStatus;           // 'pending' | 'connected' | 'error'
    private ?\DateTimeImmutable $lastSyncAt = null;
    private ?\DateTimeImmutable $createdAt = null;
    private ?\DateTimeImmutable $updatedAt = null;
}
```

#### ServiceTitanSyncLog Entity
```php
class ServiceTitanSyncLog
{
    private ?int $id = null;
    private ServiceTitanCredential $credential;
    private string $syncType;                   // 'manual' | 'scheduled'
    private string $dataType;                   // 'invoices' | 'customers' | 'both'
    private string $status;                     // 'running' | 'completed' | 'failed'
    private int $recordsProcessed = 0;
    private ?string $errorMessage = null;
    private \DateTimeImmutable $startedAt;
    private ?\DateTimeImmutable $completedAt = null;
}
```

### 4.3 OAuth Service Architecture

#### ServiceTitanAuthService
```php
class ServiceTitanAuthService
{
    public function validateCredentials(OAuthCredentials $credentials): bool;
    public function performOAuthHandshake(ServiceTitanCredential $credential): void;
    public function refreshAccessToken(ServiceTitanCredential $credential): void;
    public function isTokenExpired(ServiceTitanCredential $credential): bool;
    public function getValidAccessToken(ServiceTitanCredential $credential): string;
}
```

---

## 5. ServiceTitan API Integration

### 5.1 ServiceTitanClient Architecture

Extending the existing `DomainClient` pattern for consistency:

```php
class ServiceTitanClient extends DomainClient
{
    public function __construct(
        HttpClientInterface $httpClient,
        string $serviceTitanApiBaseUrl,
        private ServiceTitanAuthService $authService
    );

    // Override authorization to use OAuth tokens instead of API keys
    protected function getAuthorizationHeader(ServiceTitanCredential $credential): array;

    // ServiceTitan-specific API methods
    public function getCustomers(ServiceTitanCredential $credential, array $params = []): array;
    public function getInvoices(ServiceTitanCredential $credential, array $params = []): array;
    public function getCustomersPaginated(ServiceTitanCredential $credential, int $page, int $pageSize): array;
    public function getInvoicesPaginated(ServiceTitanCredential $credential, int $page, int $pageSize): array;
}
```

### 5.2 API Response Transformation

#### ServiceTitan-Specific Record Maps

**ServiceTitanInvoiceRecordMap** (extends InvoiceRecordMap):
```php
class ServiceTitanInvoiceRecordMap extends InvoiceRecordMap
{
    protected function getFieldMappings(): array
    {
        return array_merge(parent::getFieldMappings(), [
            // ServiceTitan API field mappings
            'id' => 'invoice_number',
            'customerId' => 'customer_id',
            'jobNumber' => 'job_number',
            'total' => 'total',
            'invoiceDate' => 'invoice_date',
            'customer.name' => 'customer_name',
            'customer.address.street' => 'customer_address',
            'customer.address.city' => 'customer_city',
            'customer.address.state' => 'customer_state',
            'customer.address.zip' => 'customer_zip',
            // ... additional ServiceTitan-specific mappings
        ]);
    }
}
```

**ServiceTitanMemberRecordMap** (extends MemberRecordMap):
```php
class ServiceTitanMemberRecordMap extends MemberRecordMap
{
    protected function getFieldMappings(): array
    {
        return array_merge(parent::getFieldMappings(), [
            // ServiceTitan customer API field mappings
            'id' => 'customer_id',
            'name' => 'customer_name',
            'firstName' => 'customer_first_name',
            'lastName' => 'customer_last_name',
            'address.street' => 'customer_address',
            'address.city' => 'customer_city',
            'address.state' => 'customer_state',
            'phoneNumbers[0].number' => 'customer_phone_number_primary',
            'isActive' => 'active_member',
            // ... additional ServiceTitan-specific mappings
        ]);
    }
}
```

---

## 6. Data Synchronization Services

### 6.1 ServiceTitanIntegrationService

Primary orchestration service mirroring `FieldServicesUploadService` pattern:

```php
class ServiceTitanIntegrationService
{
    private const int INSERT_RECORD_BATCH_SIZE = 2000;

    public function __construct(
        private readonly ServiceTitanClient $serviceTitanClient,
        private readonly IngestRepository $ingestRepository,
        private readonly UnificationCompanyProcessingDispatchService $jobDispatch,
        private readonly CompanyDataImportJobRepository $companyDataImportJobRepository,
        private readonly ServiceTitanAuthService $authService,
        private readonly LoggerInterface $logger,
    ) {}

    public function syncInvoiceData(ServiceTitanCredential $credential, ?int $importId = null): int;
    public function syncCustomerData(ServiceTitanCredential $credential, ?int $importId = null): int;
    public function performFullSync(ServiceTitanCredential $credential): void;
}
```

### 6.2 Batch Processing Implementation

Following the exact pattern from `FieldServicesUploadService`:

```php
public function syncInvoiceData(ServiceTitanCredential $credential, ?int $importId = null): int
{
    $company = $credential->getCompany();
    $recordCounter = 0;
    $recordsToAdd = [];
    
    // Get paginated data from ServiceTitan API
    foreach ($this->getPaginatedInvoices($credential) as $apiInvoice) {
        // Transform API response to existing record format
        $row = $this->transformApiInvoiceToRecord($apiInvoice);
        $row['tenant'] = $company->getIntacctId();
        $row['trade'] = $company->getPrimaryTrade()->getLongName();
        $row['software'] = 'ServiceTitan';
        $row['hub_plus_import_id'] = $importId;

        /** @var InvoiceRecord $invoiceRecord */
        $invoiceRecord = InvoiceRecord::fromTabularRecord($row);

        try {
            $invoiceRecord->processCustomerNames();
            $invoiceRecord->validateFieldValues();
        } catch (\Throwable $e) {
            $this->logger->warning('Skipping invoice record', ['error' => $e->getMessage()]);
            continue;
        }

        $recordsToAdd[] = $invoiceRecord;
        ++$recordCounter;

        // Identical batch processing logic from FieldServicesUploadService
        if (0 === $recordCounter % self::INSERT_RECORD_BATCH_SIZE) {
            $this->ingestRepository->insertInvoiceRecords($recordsToAdd);
            $recordsToAdd = [];
            
            $progress = "Synced {$recordCounter} invoice records so far";
            $this->companyDataImportJobRepository->updateProgressPercent(
                $importId,
                $progress,
                ($recordCounter / $totalEstimated) * 50
            );
        }
    }

    // Final batch processing and downstream dispatch - identical to existing pattern
    if (count($recordsToAdd) > 0) {
        $this->ingestRepository->insertInvoiceRecords($recordsToAdd);
    }

    if ($recordCounter > 0) {
        $finalText = "Processed {$recordCounter} invoice records in total";
        $this->companyDataImportJobRepository->updateProgressPercent($importId, $finalText, 50, 'PROCESSING', true);
        $this->jobDispatch->dispatchProcessingForCompany($company);
    }

    return $recordCounter;
}
```

---

## 7. User Interface Architecture

### 7.1 Credential Management Controllers

#### Feature: CredentialManagement
```
Controller/
├── CreateServiceTitanCredentialController.php
├── UpdateServiceTitanCredentialController.php
├── DeleteServiceTitanCredentialController.php
├── GetServiceTitanCredentialsController.php
├── TestServiceTitanConnectionController.php
└── GetCredentialManagementMetadataController.php
```

#### Feature: DataSynchronization
```
Controller/
├── TriggerManualSyncController.php
├── GetSyncHistoryController.php
├── GetSyncStatusController.php
├── UpdateSyncScheduleController.php
└── GetSynchronizationDashboardController.php
```

### 7.2 API Endpoints Design

#### Credential Management Endpoints
- `POST /api/servicetitan/credentials` - Create new ServiceTitan credentials
- `GET /api/servicetitan/credentials` - List all credentials for authenticated user's companies
- `PUT /api/servicetitan/credentials/{id}` - Update existing credentials
- `DELETE /api/servicetitan/credentials/{id}` - Remove credentials
- `POST /api/servicetitan/credentials/{id}/test` - Test connection validity

#### Synchronization Endpoints
- `POST /api/servicetitan/sync/{credentialId}` - Trigger manual sync
- `GET /api/servicetitan/sync/history` - Get sync history for all credentials
- `GET /api/servicetitan/sync/status/{credentialId}` - Get current sync status
- `GET /api/servicetitan/dashboard` - Get synchronization dashboard data

### 7.3 DTO Architecture

#### Request DTOs
```php
class CreateServiceTitanCredentialDTO
{
    public string $tenantId;
    public string $clientId;
    public string $clientSecret;
    public string $environment; // 'integration' | 'production'
    public int $companyId;
}

class TriggerSyncRequestDTO
{
    public string $syncType; // 'invoices' | 'customers' | 'both'
    public bool $forceFullSync = false;
}
```

#### Response DTOs
```php
class ServiceTitanCredentialResponseDTO
{
    public int $id;
    public string $tenantId;
    public string $clientId; // Masked for security
    public string $environment;
    public string $connectionStatus;
    public ?string $lastSyncAt;
    public CompanyDTO $company;
}

class SyncHistoryResponseDTO
{
    public int $id;
    public string $syncType;
    public string $dataType;
    public string $status;
    public int $recordsProcessed;
    public ?string $errorMessage;
    public string $startedAt;
    public ?string $completedAt;
}
```

---

## 8. Security Architecture

### 8.1 Credential Encryption

Using Symfony's encryption capabilities for secure credential storage:

```php
class ServiceTitanCredentialEncryption
{
    public function __construct(
        private readonly string $encryptionKey,
        private readonly string $encryptionMethod = 'AES-256-CBC'
    ) {}

    public function encrypt(string $value): string;
    public function decrypt(string $encryptedValue): string;
}
```

### 8.2 Access Control

#### ServiceTitan Security Voters
```php
class ServiceTitanCredentialVoter extends Voter
{
    // Only company administrators can manage ServiceTitan credentials
    // Read access for company users to view sync status
    protected function supports(string $attribute, mixed $subject): bool;
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool;
}
```

### 8.3 Audit Logging

All ServiceTitan operations logged through existing `AuditLogCreationService`:
- Credential creation/updates/deletions
- Connection tests and OAuth handshakes
- Sync operations and failures
- Token refresh operations

---

## 9. Database Schema Design

### 9.1 New Tables

#### servicetitan_credentials
```sql
CREATE TABLE servicetitan_credentials (
    id SERIAL PRIMARY KEY,
    company_id INTEGER NOT NULL REFERENCES companies(id),
    tenant_id VARCHAR(255) NOT NULL,
    client_id TEXT NOT NULL,                    -- Encrypted
    client_secret TEXT NOT NULL,               -- Encrypted
    environment VARCHAR(50) NOT NULL,          -- 'integration' | 'production'
    access_token TEXT,                         -- Encrypted, nullable
    refresh_token TEXT,                        -- Encrypted, nullable
    token_expires_at TIMESTAMP WITH TIME ZONE,
    connection_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    last_sync_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    
    UNIQUE(company_id, environment),           -- One credential per company per environment
    CHECK (environment IN ('integration', 'production')),
    CHECK (connection_status IN ('pending', 'connected', 'error'))
);
```

#### servicetitan_sync_logs
```sql
CREATE TABLE servicetitan_sync_logs (
    id SERIAL PRIMARY KEY,
    credential_id INTEGER NOT NULL REFERENCES servicetitan_credentials(id),
    sync_type VARCHAR(50) NOT NULL,            -- 'manual' | 'scheduled'
    data_type VARCHAR(50) NOT NULL,            -- 'invoices' | 'customers' | 'both'
    status VARCHAR(50) NOT NULL,               -- 'running' | 'completed' | 'failed'
    records_processed INTEGER NOT NULL DEFAULT 0,
    error_message TEXT,
    started_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
    completed_at TIMESTAMP WITH TIME ZONE,
    
    CHECK (sync_type IN ('manual', 'scheduled')),
    CHECK (data_type IN ('invoices', 'customers', 'both')),
    CHECK (status IN ('running', 'completed', 'failed'))
);
```

### 9.2 Data Source Identification

Reusing existing database schema with data source tracking:
- **invoices_stream**: Add `software = 'ServiceTitan'` to distinguish API-sourced data
- **members_stream**: Add `software = 'ServiceTitan'` to distinguish API-sourced data
- **No schema changes required** - API data flows through existing tables

---

## 10. Command Architecture

### 10.1 Scheduled Synchronization Commands

#### ProcessServiceTitanSyncCommand
```php
#[AsCommand(name: 'servicetitan:sync:process')]
class ProcessServiceTitanSyncCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('credential-id', null, InputOption::VALUE_OPTIONAL, 'Specific credential ID to sync');
        $this->addOption('sync-type', null, InputOption::VALUE_OPTIONAL, 'Data type to sync: invoices|customers|both', 'both');
        $this->addOption('environment', null, InputOption::VALUE_OPTIONAL, 'Environment filter: integration|production');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Process scheduled synchronizations
        // Support for filtering by credential, sync type, or environment
        // Comprehensive error handling and logging
    }
}
```

### 10.2 Management Commands

#### ServiceTitanCredentialManagementCommand
```php
#[AsCommand(name: 'servicetitan:credentials:manage')]
class ServiceTitanCredentialManagementCommand extends Command
{
    // CLI interface for credential management
    // Useful for initial setup and troubleshooting
    // Support for testing connections and viewing status
}
```

---

## 11. Error Handling and Resilience

### 11.1 Exception Hierarchy

Following established Hub Plus API patterns:

```php
// OAuth-specific exceptions
class ServiceTitanAuthException extends AppException {}
class InvalidCredentialsException extends ServiceTitanAuthException {}
class TokenRefreshFailedException extends ServiceTitanAuthException {}
class OAuthHandshakeFailedException extends ServiceTitanAuthException {}

// API communication exceptions
class ServiceTitanAPIException extends APICommunicationException {}
class RateLimitExceededException extends ServiceTitanAPIException {}
class ServiceTitanServiceUnavailableException extends ServiceTitanAPIException {}

// Data processing exceptions
class ServiceTitanDataMappingException extends AppException {}
class InvalidApiResponseFormatException extends ServiceTitanDataMappingException {}
```

### 11.2 Retry and Recovery Mechanisms

#### Exponential Backoff for API Calls
```php
class ServiceTitanApiRetryService
{
    public function executeWithRetry(callable $apiCall, int $maxRetries = 3): mixed;
    public function calculateBackoffDelay(int $attemptNumber): int;
    public function isRetryableException(\Throwable $exception): bool;
}
```

#### Automatic Token Refresh
```php
class ServiceTitanAuthService
{
    public function ensureValidToken(ServiceTitanCredential $credential): void
    {
        if ($this->isTokenExpired($credential)) {
            $this->refreshAccessToken($credential);
        }
    }
}
```

---

## 12. Testing Strategy

### 12.1 Test-Driven Development Approach

Following established Hub Plus API TDD practices with real database operations:

#### Repository Tests (AbstractKernelTestCase)
```php
class ServiceTitanCredentialRepositoryTest extends AbstractKernelTestCase
{
    private ServiceTitanCredentialRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(ServiceTitanCredentialRepository::class);
    }

    public function testFindCredentialsByCompanyAndEnvironment(): void
    {
        // Test actual database operations with real entities
    }
}
```

#### Service Tests (Real Dependencies)
```php
class ServiceTitanIntegrationServiceTest extends AbstractKernelTestCase
{
    private ServiceTitanIntegrationService $integrationService;
    private IngestRepository $ingestRepository;

    public function setUp(): void
    {
        parent::setUp();
        
        // Use real repository instances - no mocking
        $this->ingestRepository = $this->getRepository(IngestRepository::class);
        $this->integrationService = $this->getService(ServiceTitanIntegrationService::class);
    }

    public function testSyncInvoiceDataWithRealDatabase(): void
    {
        // Test complete data flow with actual database persistence
    }
}
```

### 12.2 Integration Testing

#### ServiceTitan API Integration Tests
```php
class ServiceTitanClientIntegrationTest extends AbstractKernelTestCase
{
    public function testOAuthHandshakeWithIntegrationEnvironment(): void
    {
        // Test against ServiceTitan Integration environment
        // Validate complete OAuth flow
    }

    public function testDataExtractionWithRealCredentials(): void
    {
        // Validate API data extraction and transformation
        // Ensure API responses map correctly to InvoiceRecord/MemberRecord
    }
}
```

---

## 13. Performance and Scalability

### 13.1 API Rate Limiting

ServiceTitan API rate limits managed through:
- Request throttling with configurable delays
- Exponential backoff on rate limit responses
- Queue-based processing for high-volume synchronizations

```php
class ServiceTitanRateLimitManager
{
    public function __construct(
        private readonly int $requestsPerMinute = 100,
        private readonly int $burstLimit = 10
    ) {}

    public function waitForRateLimit(): void;
    public function trackRequest(): void;
    public function isRateLimited(): bool;
}
```

### 13.2 Large Dataset Handling

Following existing patterns for efficient processing:
- **Pagination**: Process API responses in configurable page sizes
- **Batch Processing**: 2000-record batches for database operations
- **Progress Tracking**: Real-time updates via existing infrastructure
- **Memory Management**: Stream processing for large datasets

### 13.3 Caching Strategy

OAuth token caching for performance:
- In-memory token storage during sync operations
- Database persistence for token refresh
- Automatic cleanup of expired tokens

---

## 14. Monitoring and Observability

### 14.1 Logging Strategy

Comprehensive logging following existing patterns:

```php
// OAuth operations
$this->logger->info('ServiceTitan OAuth handshake initiated', [
    'tenant_id' => $credential->getTenantId(),
    'environment' => $credential->getEnvironment(),
    'company_id' => $credential->getCompany()->getId()
]);

// Data synchronization
$this->logger->info('ServiceTitan sync completed', [
    'credential_id' => $credential->getId(),
    'records_processed' => $recordCount,
    'sync_duration' => $duration,
    'data_type' => $syncType
]);

// Error scenarios
$this->logger->error('ServiceTitan API error', [
    'error' => $exception->getMessage(),
    'api_endpoint' => $endpoint,
    'response_code' => $responseCode
]);
```

### 14.2 Metrics and Alerting

Integration with existing monitoring:
- Sync success/failure rates
- API response times and error rates
- OAuth token refresh frequency
- Data processing throughput
- Connection status monitoring per tenant

---

## 15. Deployment Strategy

### 15.1 Environment Configuration

Environment-specific configuration management:

```yaml
# config/packages/servicetitan.yaml
servicetitan:
    api:
        integration:
            base_url: 'https://api-integration.servicetitan.io'
        production:
            base_url: 'https://api.servicetitan.io'
    oauth:
        token_refresh_threshold: 300  # seconds before expiry
        connection_timeout: 30
        request_timeout: 60
    sync:
        default_page_size: 100
        max_page_size: 1000
        rate_limit_per_minute: 100
```

### 15.2 Feature Flags

Gradual rollout capability:
- Per-company feature enablement
- Environment-specific rollouts
- Emergency disable switches
- A/B testing for sync frequency optimization

### 15.3 Migration Strategy

Safe deployment with rollback capability:
- Database migrations for new tables
- Backward compatibility with existing data processing
- Phased rollout starting with test companies
- Manual override to revert to file-based processing

---

## 16. Maintenance and Operations

### 16.1 Credential Rotation

Support for periodic credential updates:
- UI for credential rotation
- Notification before credential expiry
- Seamless token refresh during rotation
- Audit trail for all credential changes

### 16.2 API Version Management

Future-proofing for ServiceTitan API changes:
- Version-specific client implementations
- Backward compatibility layers
- Automated testing against multiple API versions
- Migration tools for API version upgrades

### 16.3 Data Reconciliation

Tools for validating API vs. manual data:
- Comparison utilities for data accuracy verification
- Discrepancy reporting and resolution
- Historical data validation
- Data quality metrics and monitoring

---

## 17. Risk Mitigation

### 17.1 OAuth Complexity Management

Mitigating the primary technical risk:
- Comprehensive test coverage for OAuth flows
- Clear documentation for client onboarding process
- Automated credential validation and testing
- Support tools for troubleshooting OAuth issues

### 17.2 API Dependency Management

Reducing ServiceTitan API risks:
- Comprehensive error handling and retry logic
- Fallback to manual processing capability
- API health monitoring and alerting
- Service degradation graceful handling

### 17.3 Data Quality Assurance

Ensuring API data quality:
- Validation rules matching existing file processing
- Data transformation verification tests
- Comparison tools for manual vs. API data
- Quality metrics and anomaly detection

---

## 18. Success Metrics

### 18.1 Technical Metrics
- **Infrastructure Reuse**: Achieve 80% reuse of existing Hub Plus API components
- **API Response Time**: 95% of API calls complete within 5 seconds
- **Data Processing**: Match existing 2000-record batch processing efficiency
- **Error Rate**: Less than 1% failure rate for properly configured integrations

### 18.2 Operational Metrics
- **Setup Time**: Complete ServiceTitan integration setup in under 30 minutes
- **Sync Reliability**: 99% successful sync completion rate
- **Data Accuracy**: 100% data equivalence between API and manual processing
- **Support Load**: Minimal increase in support tickets related to data synchronization

---

## 19. Future Enhancement Roadmap

### 19.1 Post-MVP Enhancements
- **Real-time Webhooks**: ServiceTitan webhook integration for immediate data updates
- **Advanced Scheduling**: Business rules-based scheduling (avoid weekends, holidays)
- **Custom Data Transformation**: Client-specific field mapping and transformation rules
- **Bulk Operations**: Mass client onboarding and management tools

### 19.2 Platform Expansion
- **Multi-Platform Template**: Use ServiceTitan architecture as template for additional field service integrations
- **Enhanced Reporting**: Custom reports using integrated ServiceTitan data
- **Workflow Automation**: Trigger business processes based on ServiceTitan data changes
- **Advanced Analytics**: Cross-platform data analytics and insights

---

## 20. Conclusion

This technical architecture provides a comprehensive blueprint for implementing ServiceTitan API integration while maximizing reuse of existing Hub Plus API infrastructure. The design addresses the primary architectural challenge of multi-tenant OAuth management while ensuring seamless integration with existing data processing pipelines.

### Key Architectural Strengths
1. **Maximum Infrastructure Reuse**: 80% reuse target achieved through adapter pattern implementation
2. **OAuth Complexity Management**: Secure, scalable multi-tenant credential management
3. **Seamless Integration**: API-sourced data indistinguishable from file-sourced data
4. **Modular Design**: Self-contained module with clear interfaces and separation of concerns
5. **Production-Ready**: Comprehensive error handling, monitoring, and operational capabilities

### Implementation Readiness
The architecture provides detailed specifications for:
- Complete module structure and component design
- Database schema and entity relationships
- Service layer architecture and data flow
- User interface design and API endpoints
- Security, testing, and deployment strategies

This architecture serves as the foundation for implementing the ServiceTitan integration requirements while maintaining the high quality and reliability standards of the Hub Plus API platform.

---

**Document Status**: ✅ Complete - Ready for Development Phase
**Next Phase**: Implementation Planning and OAuth Infrastructure Development
**Approval Required**: Technical Lead and Platform Architecture review
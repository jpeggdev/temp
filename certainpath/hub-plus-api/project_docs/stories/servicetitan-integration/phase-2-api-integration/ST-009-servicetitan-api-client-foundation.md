# User Story: ST-009 - ServiceTitan API Client Foundation

## Story Information
- **Story ID**: ST-009
- **Epic**: ServiceTitan API Integration
- **Phase**: Phase 2 - ServiceTitan API Client & Data Transformation
- **Story Points**: 8
- **Priority**: Must Have
- **Component**: Client Layer

## User Story
**As a** system integrator  
**I want** a robust ServiceTitan API client  
**So that** I can reliably communicate with ServiceTitan endpoints

## Detailed Description
This story creates the foundational API client for communicating with ServiceTitan's REST API. The client extends the existing DomainClient pattern and provides OAuth authentication, rate limiting, error handling, and environment-specific configuration.

## Acceptance Criteria
- [ ] Create `ServiceTitanClient` extending existing `DomainClient`
- [ ] Implement OAuth header authentication
- [ ] Add rate limiting and request throttling
- [ ] Include comprehensive error handling with retries
- [ ] Support both Integration and Production environments
- [ ] Add request/response logging for debugging

## Technical Implementation Notes
- **Client Location**: `src/Module/ServiceTitan/Client/ServiceTitanClient.php`
- **Pattern**: Extend existing `DomainClient` pattern
- **Architecture Reference**: Section 5.1

### Core Client Methods
```php
class ServiceTitanClient extends DomainClient
{
    public function get(string $endpoint, array $params = []): ApiResponse
    public function post(string $endpoint, array $data = []): ApiResponse
    public function put(string $endpoint, array $data = []): ApiResponse
    public function delete(string $endpoint): ApiResponse
    
    // ServiceTitan-specific methods
    public function getCustomers(array $filters = []): CustomerResponse
    public function getInvoices(array $filters = []): InvoiceResponse
    public function testConnection(): ConnectionTestResponse
}
```

### Authentication Integration
- Automatic OAuth token injection in request headers
- Token expiry detection and automatic refresh
- Integration with ServiceTitanAuthService
- Support for both environment types

### Rate Limiting Features
- Configurable rate limits per environment
- Request throttling with delays
- Burst limit handling
- Rate limit exceeded response handling

### Error Handling
- HTTP status code specific error handling
- Exponential backoff retry logic (1s, 2s, 4s, 8s)
- Network timeout handling
- ServiceTitan API error response parsing

## Definition of Done
- [ ] Client class created with OAuth authentication
- [ ] Rate limiting implemented
- [ ] Error handling with exponential backoff
- [ ] Environment-specific configuration
- [ ] Unit tests with mocked HTTP responses
- [ ] Integration tests with ServiceTitan API
- [ ] Request/response logging working
- [ ] Token refresh integration
- [ ] Connection testing functional
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-004**: OAuth Authentication Service
- **ST-006**: Credential Encryption Service

## Testing Requirements
- Unit tests with mocked HTTP client
- Unit tests for rate limiting logic
- Unit tests for error handling scenarios
- Integration tests with ServiceTitan Integration environment
- Test OAuth token injection and refresh
- Test environment switching

### Mock Testing Strategy
```php
class ServiceTitanClientTest extends TestCase
{
    public function testGetCustomersWithValidResponse(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMockResponse(200, $customerData);
        
        $client = new ServiceTitanClient($mockHttpClient, $authService);
        $response = $client->getCustomers(['status' => 'active']);
        
        self::assertInstanceOf(CustomerResponse::class, $response);
    }
}
```

### Integration Testing Strategy
```php
class ServiceTitanClientIntegrationTest extends AbstractKernelTestCase
{
    public function testConnectionToIntegrationEnvironment(): void
    {
        $client = $this->getService(ServiceTitanClient::class);
        $credential = $this->createTestCredential();
        
        $response = $client->testConnection();
        
        self::assertTrue($response->isSuccessful());
    }
}
```

## Configuration Requirements

### Environment Configuration
```yaml
# config/packages/servicetitan.yaml
servicetitan:
    api:
        integration:
            base_url: 'https://api-integration.servicetitan.io'
            timeout: 30
            rate_limit: 120 # requests per minute
        production:
            base_url: 'https://api.servicetitan.io'
            timeout: 30
            rate_limit: 120 # requests per minute
```

## Risks and Mitigation
- **Risk**: ServiceTitan API changes breaking client
- **Mitigation**: Comprehensive integration tests and versioned API endpoints
- **Risk**: Rate limiting causing failures
- **Mitigation**: Proper throttling and retry mechanisms

## Additional Notes
This client serves as the foundation for all ServiceTitan API communication. It must be robust, well-tested, and handle all error scenarios gracefully. The rate limiting and retry logic are critical for production stability.
# ServiceTitan Integration Testing Suite

## Overview

This directory contains comprehensive integration tests for the ServiceTitan integration feature. The test suite validates all aspects of the integration including OAuth flow, API endpoints, security, performance, and API contracts.

## Test Files

### Core Integration Tests

#### `ServiceTitanOAuthFlowIntegrationTest.php`
- **Purpose**: Tests complete OAuth 2.0 handshake flow
- **Coverage**: 
  - Authorization URL generation
  - Token exchange
  - Token refresh
  - Connection validation
  - Error handling
  - Multi-environment support
  - Concurrent OAuth flows

#### `ServiceTitanControllerIntegrationTest.php`
- **Purpose**: Tests all ServiceTitan API endpoints
- **Coverage**:
  - Credential CRUD operations
  - Data synchronization management
  - Dashboard data retrieval
  - History and status endpoints
  - Security access control
  - Cross-company isolation

#### `ServiceTitanPerformanceTest.php`
- **Purpose**: Validates performance benchmarks
- **Coverage**:
  - Response time validation
  - Memory usage monitoring
  - Concurrent request handling
  - Large dataset processing
  - Real-time status updates
  - Pagination performance

#### `ServiceTitanSecurityTest.php`
- **Purpose**: Comprehensive security validation
- **Coverage**:
  - Authentication requirements
  - Authorization controls
  - Data isolation
  - Input sanitization
  - Rate limiting
  - Audit logging
  - Session security

#### `ServiceTitanApiContractTest.php`
- **Purpose**: API contract validation
- **Coverage**:
  - Request/response structure validation
  - DTO contract compliance
  - Error response formatting
  - Content type handling
  - UTF-8 encoding support

### Test Coordination

#### `ServiceTitanIntegrationTestSuite.php`
- **Purpose**: Master test coordinator
- **Features**:
  - Test execution planning
  - Environment validation
  - Service availability checks
  - Database schema validation
  - Performance benchmarking
  - Security compliance checklist

## Running the Tests

### Individual Test Files

```bash
# Run OAuth flow tests
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ServiceTitanOAuthFlowIntegrationTest.php

# Run controller endpoint tests
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ServiceTitanControllerIntegrationTest.php

# Run performance tests
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ServiceTitanPerformanceTest.php

# Run security tests
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ServiceTitanSecurityTest.php

# Run API contract tests
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ServiceTitanApiContractTest.php
```

### Complete Integration Suite

```bash
# Run all ServiceTitan integration tests
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/

# Run with detailed output
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ --testdox

# Run with coverage
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ --coverage-html coverage/servicetitan
```

### Master Test Suite

```bash
# Run the comprehensive test suite coordinator
vendor/bin/phpunit tests/Module/ServiceTitan/Integration/ServiceTitanIntegrationTestSuite.php
```

## Test Architecture

### Base Classes Used

- **AbstractKernelTestCase**: For service integration tests
- **AbstractWebTestCase**: For HTTP endpoint tests
- **AbstractDatabaseTool**: For database operations

### Test Data Management

All tests use:
- Real database operations (no mocking of repositories)
- Automatic database schema reset between tests
- Test fixtures for consistent data
- Faker for dynamic test data generation

### Service Injection

Tests use the service container to get real service instances:

```php
// Get services
$authService = $this->getService(ServiceTitanAuthService::class);

// Get repositories  
$credentialRepo = $this->getRepository(ServiceTitanCredentialRepository::class);
```

## API Endpoints Tested

### Credential Management
- `POST /api/servicetitan/credentials` - Create credential
- `GET /api/servicetitan/credentials` - List credentials
- `GET /api/servicetitan/credentials/{id}` - Get credential
- `PUT /api/servicetitan/credentials/{id}` - Update credential
- `DELETE /api/servicetitan/credentials/{id}` - Delete credential
- `POST /api/servicetitan/credentials/{id}/test` - Test connection

### Data Synchronization
- `POST /api/servicetitan/credentials/{id}/sync` - Trigger sync
- `GET /api/servicetitan/credentials/{id}/sync/status` - Get sync status
- `GET /api/servicetitan/credentials/{id}/sync/history` - Get sync history
- `DELETE /api/servicetitan/credentials/{id}/sync/current` - Cancel sync
- `GET /api/servicetitan/credentials/{id}/sync/metrics` - Get metrics

### Dashboard
- `GET /api/servicetitan/dashboard` - Get dashboard data

## Security Test Coverage

### Authentication
- Unauthenticated access denied
- Expired token handling
- Invalid token format rejection

### Authorization
- Role-based access control
- Company data isolation
- Cross-company access prevention
- Permission-based operation control

### Data Protection
- Credential masking in responses
- Sensitive data not in logs
- Input sanitization
- Rate limiting enforcement

## Performance Benchmarks

### Response Time Targets
- Credential listing: < 500ms
- Dashboard loading: < 2000ms
- Sync status: < 100ms
- OAuth completion: < 5000ms

### Memory Usage Limits
- Dashboard with 50+ credentials: < 10MB
- Large dataset pagination: < 20MB
- Credential listing: < 5MB

### Concurrency Support
- Multiple sync status requests
- Concurrent OAuth flows
- Cross-user isolation

## Error Scenarios Tested

### OAuth Errors
- Invalid authorization codes
- Expired refresh tokens
- Network timeouts
- Rate limit exceeded

### API Errors
- Validation failures
- Resource not found
- Unauthorized access
- Malformed requests

### System Errors
- Database connection issues
- Service unavailability
- Configuration problems

## Test Data Patterns

### Test Credentials
```php
$credential = new ServiceTitanCredential();
$credential->setCompany($testCompany);
$credential->setClientId('test-client-id-' . uniqid());
$credential->setClientSecret('test-client-secret-' . uniqid());
$credential->setEnvironment(ServiceTitanEnvironment::INTEGRATION);
```

### Test Sync Logs
```php
$syncLog = new ServiceTitanSyncLog();
$syncLog->setCredential($credential);
$syncLog->setSyncType(ServiceTitanSyncType::MANUAL);
$syncLog->setDataType(ServiceTitanSyncDataType::BOTH);
$syncLog->setStatus(ServiceTitanSyncStatus::COMPLETED);
```

## Integration with CI/CD

### Pre-commit Hooks
Tests automatically run before commits to ensure:
- All integration tests pass
- Performance benchmarks met
- Security requirements satisfied
- API contracts maintained

### Deployment Validation
Integration tests run during deployment to validate:
- Service connectivity
- Database migrations
- Configuration correctness
- End-to-end functionality

## Debugging Test Failures

### Common Issues

1. **Service Not Found**
   - Verify service configuration in `services.yaml`
   - Check class exists and is autoloaded

2. **Database Schema Issues**
   - Run `doctrine:migrations:migrate` in test environment
   - Verify test database isolation

3. **Authentication Failures**
   - Check test user creation helpers
   - Verify JWT configuration for tests

4. **Performance Test Failures**
   - Check system load during test execution
   - Verify database has sufficient test data

### Test Environment Setup

```bash
# Ensure test environment
export APP_ENV=test

# Run database migrations
bin/console doctrine:migrations:migrate --env=test

# Clear test cache
bin/console cache:clear --env=test
```

## Future Enhancements

### Planned Additions
- Real-time WebSocket testing
- Load testing with multiple concurrent users
- End-to-end testing with external ServiceTitan sandbox
- Automated API documentation generation from tests

### Test Coverage Goals
- 100% line coverage for integration components
- All error scenarios covered
- All security vectors tested
- Performance regression detection

## Contributing

When adding new ServiceTitan features:

1. **Add integration tests** for all new endpoints
2. **Update security tests** for new access patterns
3. **Add performance benchmarks** for new operations
4. **Validate API contracts** for new DTOs
5. **Document test scenarios** in this README

Follow the established patterns in existing test files for consistency and maintainability.
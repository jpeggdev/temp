# User Story: ST-004 - OAuth Authentication Service

## Story Information
- **Story ID**: ST-004
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 8
- **Priority**: Must Have
- **Component**: Service Layer

## User Story
**As a** system administrator  
**I want** robust OAuth token management  
**So that** API authentication is handled automatically with proper error recovery

## Detailed Description
This story implements the core OAuth authentication service for ServiceTitan API integration. The service handles the complete OAuth flow, token management, automatic refresh, and comprehensive error handling to ensure reliable API access.

## Acceptance Criteria
- [ ] Create `ServiceTitanAuthService` for OAuth operations
- [ ] Implement OAuth handshake with ServiceTitan API
- [ ] Handle automatic token refresh with expiry detection
- [ ] Validate credentials before storing
- [ ] Provide connection testing functionality
- [ ] Include comprehensive error handling and retry logic

## Technical Implementation Notes
- **Service Location**: `src/Module/ServiceTitan/Service/ServiceTitanAuthService.php`
- **HTTP Client**: Use existing HTTP client infrastructure
- **Retry Strategy**: Implement exponential backoff for retries
- **Architecture Reference**: Sections 4.3 and 11.2

### Core Methods
- `authenticateCredential(ServiceTitanCredential $credential): bool`
- `refreshAccessToken(ServiceTitanCredential $credential): bool`
- `testConnection(ServiceTitanCredential $credential): bool`
- `validateCredentials(OAuthCredentials $credentials): ValidationResult`
- `performOAuthHandshake(string $clientId, string $clientSecret, string $environment): OAuthResult`

### Error Handling
- Implement exponential backoff (1s, 2s, 4s, 8s)
- Handle rate limiting responses
- Distinguish between auth failures and service errors
- Comprehensive logging for audit trail

### Token Management
- Automatic token refresh when expiry detected
- Update credential entity with new tokens
- Handle refresh token rotation
- Track token expiry timestamps

## Definition of Done
- [ ] Service class with all OAuth operations
- [ ] Unit tests with mocked HTTP responses
- [ ] Integration tests with ServiceTitan Integration environment
- [ ] Error handling for all failure scenarios
- [ ] Proper logging for audit trail
- [ ] Exponential backoff retry logic working
- [ ] Token refresh mechanism tested
- [ ] Connection testing functional
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design
- **ST-003**: Credential Repository Implementation
- **ST-005**: Credential Management Value Objects

## Testing Requirements
- Unit tests with mocked ServiceTitan API responses
- Integration tests with actual ServiceTitan Integration environment
- Test all error scenarios (invalid credentials, network issues, etc.)
- Test token refresh flows
- Test retry logic and exponential backoff
- Test connection validation

## Risks and Mitigation
- **Risk**: ServiceTitan API changes breaking OAuth flow
- **Mitigation**: Comprehensive integration tests and error handling
- **Risk**: Rate limiting causing authentication failures
- **Mitigation**: Implement proper retry logic with exponential backoff

## Additional Notes
This is a high-risk story due to OAuth complexity. Extensive testing with ServiceTitan Integration environment is essential. The service must handle all OAuth scenarios gracefully and provide clear error messages for troubleshooting.
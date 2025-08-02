# User Story: ST-008 - OAuth Exception Hierarchy

## Story Information
- **Story ID**: ST-008
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 2
- **Priority**: Must Have
- **Component**: Exception Layer

## User Story
**As a** system developer  
**I want** specific exceptions for OAuth error scenarios  
**So that** error handling is precise and actionable

## Detailed Description
This story creates a comprehensive exception hierarchy for handling OAuth-related errors in the ServiceTitan integration. The exceptions provide specific error types, actionable messages, and proper integration with existing error handling patterns.

## Acceptance Criteria
- [ ] Create OAuth-specific exception classes
- [ ] Extend existing Hub Plus API exception patterns
- [ ] Include specific exceptions for credential validation failures
- [ ] Add exceptions for token refresh failures
- [ ] Include OAuth handshake failure exceptions
- [ ] Provide actionable error messages

## Technical Implementation Notes
- **Exception Location**: `src/Module/ServiceTitan/Feature/OAuthManagement/Exception/`
- **Pattern**: Follow existing Hub Plus API exception patterns
- **Architecture Reference**: Section 11.1

### Exception Hierarchy

#### Base Exception
```php
class ServiceTitanOAuthException extends \Exception
{
    public function __construct(
        string $message,
        private readonly ?string $actionableMessage = null,
        private readonly array $context = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

#### Specific Exception Classes
- `InvalidCredentialsException` - Invalid client ID/secret format or values
- `TokenRefreshException` - Failed to refresh access token
- `OAuthHandshakeException` - OAuth authorization flow failures
- `CredentialValidationException` - Credential validation failures
- `TokenExpiredException` - Access token expired and refresh failed
- `ServiceTitanApiException` - ServiceTitan API errors during OAuth
- `RateLimitExceededException` - OAuth rate limiting errors
- `EnvironmentConfigurationException` - Environment-specific setup issues

### Error Messages and Actions
Each exception should include:
- Clear description of what went wrong
- Actionable message for resolution
- Context data for debugging
- Appropriate HTTP status codes for API responses

## Definition of Done
- [ ] Exception hierarchy created
- [ ] All OAuth scenarios covered
- [ ] Proper error messages and codes
- [ ] Integration with existing error handling
- [ ] Unit tests for exception creation
- [ ] Documentation for error scenarios
- [ ] Actionable messages tested
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- None (foundational exceptions)

## Testing Requirements
- Unit tests for each exception type
- Unit tests for error message generation
- Unit tests for context data handling
- Integration tests for exception throwing in services
- Test actionable message generation

### Exception Examples

#### InvalidCredentialsException
```php
throw new InvalidCredentialsException(
    'ServiceTitan client credentials are invalid',
    'Please verify your Client ID and Client Secret in the ServiceTitan Developer Portal',
    ['clientId' => $credentials->getClientIdMasked()]
);
```

#### TokenRefreshException
```php
throw new TokenRefreshException(
    'Failed to refresh ServiceTitan access token',
    'The refresh token may be expired. Please re-authorize your ServiceTitan integration',
    ['credentialId' => $credential->getId()->toString()]
);
```

#### OAuthHandshakeException
```php
throw new OAuthHandshakeException(
    'ServiceTitan OAuth handshake failed',
    'Check your ServiceTitan app configuration and ensure the integration is approved',
    ['error' => $apiResponse->getError(), 'statusCode' => $apiResponse->getStatusCode()]
);
```

## Integration with Error Handling
- Exceptions should integrate with existing logging infrastructure
- HTTP API endpoints should convert exceptions to appropriate JSON responses
- Console commands should display actionable error messages
- Audit logging should capture exception context

## Risks and Mitigation
- **Risk**: Exception messages revealing sensitive information
- **Mitigation**: Careful review of error messages and context data masking

## Additional Notes
These exceptions form the foundation for comprehensive error handling throughout the ServiceTitan integration. They must provide clear, actionable guidance while maintaining security by not exposing sensitive credential information in error messages.
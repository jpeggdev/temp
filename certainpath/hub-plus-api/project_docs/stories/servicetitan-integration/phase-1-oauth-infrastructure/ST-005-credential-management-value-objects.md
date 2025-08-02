# User Story: ST-005 - Credential Management Value Objects

## Story Information
- **Story ID**: ST-005
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 3
- **Priority**: Must Have
- **Component**: Value Object Layer

## User Story
**As a** system developer  
**I want** structured data objects for credential handling  
**So that** credential data is properly validated and encapsulated

## Detailed Description
This story creates immutable value objects for handling ServiceTitan credential data throughout the application. These objects provide validation, encapsulation, and security features for credential management operations.

## Acceptance Criteria
- [ ] Create `OAuthCredentials` value object for credential data
- [ ] Implement validation for ServiceTitan credential format
- [ ] Add environment-specific validation rules
- [ ] Include credential masking for security logging
- [ ] Create immutable credential objects with proper encapsulation

## Technical Implementation Notes
- **Value Object Location**: `src/Module/ServiceTitan/ValueObject/OAuthCredentials.php`
- **Pattern**: Follow existing Hub Plus API value object patterns
- **Validation**: Implement proper validation and error messages

### OAuthCredentials Value Object
```php
class OAuthCredentials
{
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $environment,
        private readonly ?string $accessToken = null,
        private readonly ?string $refreshToken = null,
        private readonly ?\DateTimeInterface $expiresAt = null
    ) {
        $this->validate();
    }
}
```

### Validation Rules
- Client ID format validation (ServiceTitan specific)
- Client Secret format validation
- Environment validation (integration|production)
- Token format validation when provided
- Expiry date validation

### Security Features
- Credential masking for logging (`getClientIdMasked()`)
- Secure string representation (`__toString()` masks sensitive data)
- No direct access to sensitive fields without validation

## Definition of Done
- [ ] Value object classes created with validation
- [ ] Unit tests for all validation scenarios
- [ ] Proper error messages for invalid data
- [ ] Immutable design with encapsulation
- [ ] Credential masking working correctly
- [ ] Environment-specific validation rules
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- None (foundational value object)

## Testing Requirements
- Unit tests for all validation rules
- Unit tests for credential masking
- Unit tests for immutability
- Test invalid credential scenarios
- Test environment-specific validation

## Additional Value Objects

### ValidationResult
```php
class ValidationResult
{
    public function __construct(
        private readonly bool $isValid,
        private readonly array $errors = []
    ) {}
}
```

### OAuthResult
```php
class OAuthResult
{
    public function __construct(
        private readonly bool $success,
        private readonly ?string $accessToken = null,
        private readonly ?string $refreshToken = null,
        private readonly ?\DateTimeInterface $expiresAt = null,
        private readonly ?string $errorMessage = null
    ) {}
}
```

## Risks and Mitigation
- **Risk**: Credential data exposure in logs
- **Mitigation**: Comprehensive masking in all string representations

## Additional Notes
These value objects provide the foundation for secure credential handling throughout the application. They must be thoroughly tested for security and validation to prevent credential exposure or invalid data processing.
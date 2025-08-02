# User Story: ST-006 - Credential Encryption Service

## Story Information
- **Story ID**: ST-006
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 5
- **Priority**: Must Have
- **Component**: Security Layer

## User Story
**As a** security administrator  
**I want** enterprise-grade credential encryption  
**So that** sensitive OAuth credentials are protected at rest

## Detailed Description
This story implements a comprehensive encryption service for protecting ServiceTitan OAuth credentials at rest in the database. The service uses enterprise-grade encryption standards and secure key management to ensure credential security.

## Acceptance Criteria
- [ ] Create encryption service for credential protection
- [ ] Use AES-256-CBC encryption for sensitive fields
- [ ] Implement secure key management
- [ ] Add encryption/decryption for database operations
- [ ] Include audit logging for encryption operations
- [ ] Handle encryption errors gracefully

## Technical Implementation Notes
- **Service Location**: `src/Module/ServiceTitan/Service/ServiceTitanCredentialEncryption.php`
- **Encryption**: Use Symfony security component
- **Algorithm**: AES-256-CBC with secure initialization vectors
- **Key Management**: Environment-specific encryption keys
- **Architecture Reference**: Section 8.1

### Core Methods
- `encrypt(string $data): string`
- `decrypt(string $encryptedData): string`
- `encryptCredential(ServiceTitanCredential $credential): void`
- `decryptCredential(ServiceTitanCredential $credential): void`
- `isEncrypted(string $data): bool`

### Security Features
- Unique initialization vector for each encryption operation
- Key rotation support for future security updates
- Constant-time comparison for encrypted data validation
- Secure memory handling for sensitive operations

### Key Management
- Environment-specific encryption keys in .env files
- Key format validation on service initialization
- Support for key rotation without data loss
- Secure key storage recommendations

## Definition of Done
- [ ] Encryption service with AES-256-CBC implementation
- [ ] Unit tests for encryption/decryption operations
- [ ] Integration with entity persistence
- [ ] Secure key management
- [ ] Error handling for encryption failures
- [ ] Audit logging for security operations
- [ ] Key rotation testing
- [ ] Performance benchmarks for encryption operations
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design

## Testing Requirements
- Unit tests for encryption/decryption accuracy
- Unit tests for error handling scenarios
- Integration tests with entity persistence
- Security tests for key management
- Performance tests for encryption overhead
- Test key rotation scenarios

### Security Testing
- Verify encrypted data is not readable without key
- Test initialization vector uniqueness
- Validate secure memory handling
- Test error scenarios don't leak sensitive data

## Risks and Mitigation
- **Risk**: Encryption key compromise
- **Mitigation**: Environment-specific keys with rotation capability
- **Risk**: Performance impact from encryption
- **Mitigation**: Benchmark encryption operations and optimize if needed

## Configuration Requirements

### Environment Variables (.env)
```
SERVICETITAN_ENCRYPTION_KEY=base64:generated-256-bit-key
SERVICETITAN_ENCRYPTION_ALGORITHM=AES-256-CBC
```

### Service Configuration
```yaml
# config/services.yaml
servicetitan.credential_encryption:
    class: App\Module\ServiceTitan\Service\ServiceTitanCredentialEncryption
    arguments:
        $encryptionKey: '%env(SERVICETITAN_ENCRYPTION_KEY)%'
        $algorithm: '%env(SERVICETITAN_ENCRYPTION_ALGORITHM)%'
```

## Additional Notes
This service is critical for security compliance and must implement industry-standard encryption practices. The encryption must be transparent to the application layer while providing robust protection for sensitive credential data.
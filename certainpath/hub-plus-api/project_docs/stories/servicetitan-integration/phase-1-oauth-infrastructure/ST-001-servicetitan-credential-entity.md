# User Story: ST-001 - ServiceTitan Credential Entity Design

## Story Information
- **Story ID**: ST-001
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 5
- **Priority**: Must Have
- **Component**: Entity Layer

## User Story
**As a** system architect  
**I want** to design secure credential storage entities  
**So that** multi-tenant ServiceTitan credentials are properly managed with encryption

## Detailed Description
This story establishes the foundational data structure for storing ServiceTitan OAuth credentials in a secure, multi-tenant environment. The entity design must support multiple companies with different ServiceTitan instances while maintaining proper encryption and audit trails.

## Acceptance Criteria
- [ ] Create `ServiceTitanCredential` entity with encrypted fields
- [ ] Implement multi-tenant association (Company relationship)
- [ ] Support environment-specific credentials (Integration/Production)
- [ ] Include connection status tracking and audit timestamps
- [ ] Add unique constraints for company/environment combinations
- [ ] Implement proper encryption/decryption for sensitive fields

## Technical Implementation Notes
- **Entity Location**: `src/Module/ServiceTitan/Entity/ServiceTitanCredential.php`
- **Database Table**: `servicetitan_credentials`
- **Encryption**: Use Symfony encryption service for `clientId`, `clientSecret`, `accessToken`
- **Architecture Reference**: Sections 4.2 and 9.1

### Required Fields
- `id` (UUID)
- `company` (ManyToOne relationship)
- `environment` (enum: integration, production)
- `clientId` (encrypted string)
- `clientSecret` (encrypted string)
- `accessToken` (encrypted string, nullable)
- `refreshToken` (encrypted string, nullable)
- `tokenExpiresAt` (datetime, nullable)
- `connectionStatus` (enum: active, inactive, error)
- `lastConnectionAttempt` (datetime, nullable)
- `createdAt` (datetime)
- `updatedAt` (datetime)

### Constraints
- Unique constraint on (company, environment)
- Foreign key constraint to Company entity

## Definition of Done
- [ ] Entity class created with all required fields
- [ ] Database migration generated and tested
- [ ] Unit tests for entity methods
- [ ] Encryption/decryption working correctly
- [ ] Unique constraints enforced
- [ ] Proper relationships with Company entity established
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- None (foundational story)

## Testing Requirements
- Unit tests for entity validation
- Unit tests for encryption/decryption methods
- Database constraint testing
- Multi-tenant data isolation testing

## Risks and Mitigation
- **Risk**: Encryption key management complexity
- **Mitigation**: Use Symfony's proven encryption service with environment-specific keys

## Additional Notes
This entity forms the foundation for all ServiceTitan credential management and must be thoroughly tested for security and data integrity before proceeding to dependent stories.
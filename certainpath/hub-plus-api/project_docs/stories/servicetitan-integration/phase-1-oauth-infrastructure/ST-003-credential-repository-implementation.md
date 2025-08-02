# User Story: ST-003 - Credential Repository Implementation

## Story Information
- **Story ID**: ST-003
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 5
- **Priority**: Must Have
- **Component**: Repository Layer

## User Story
**As a** system developer  
**I want** repositories for credential and sync log management  
**So that** database operations follow established patterns

## Detailed Description
This story implements the repository layer for ServiceTitan credential and sync log entities, providing optimized database queries and following Hub Plus API repository patterns. The repositories will serve as the primary interface for all credential-related database operations.

## Acceptance Criteria
- [ ] Create `ServiceTitanCredentialRepository` extending Doctrine repository
- [ ] Implement credential lookup by company and environment
- [ ] Add connection status filtering methods
- [ ] Create `ServiceTitanSyncLogRepository` for sync history
- [ ] Include pagination support for sync history queries
- [ ] Add credential validation and expiry checking methods

## Technical Implementation Notes
- **Repository Location**: `src/Module/ServiceTitan/Repository/`
- **Testing Strategy**: Use AbstractKernelTestCase for repository testing
- **Pattern**: Follow existing Hub Plus API repository patterns
- **Architecture Reference**: Section 12.1

### ServiceTitanCredentialRepository Methods
- `findByCompanyAndEnvironment(Company $company, string $environment): ?ServiceTitanCredential`
- `findActiveCredentials(): array`
- `findExpiredCredentials(): array`
- `findByConnectionStatus(string $status): array`
- `validateCredentialUniqueness(Company $company, string $environment): bool`

### ServiceTitanSyncLogRepository Methods
- `findByCredential(ServiceTitanCredential $credential, int $limit = 50): array`
- `findByDateRange(\DateTime $start, \DateTime $end): array`
- `findFailedSyncs(): array`
- `findPaginatedHistory(ServiceTitanCredential $credential, int $page, int $limit): array`
- `getPerformanceMetrics(ServiceTitanCredential $credential): array`

## Definition of Done
- [ ] Repository classes created with all required methods
- [ ] Integration tests using real database operations
- [ ] Proper error handling for database operations
- [ ] Performance optimized queries
- [ ] Pagination working correctly
- [ ] All repository methods tested
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design
- **ST-002**: ServiceTitan Sync Log Entity Design

## Testing Requirements
- Integration tests using AbstractKernelTestCase
- Test all finder methods with various scenarios
- Test pagination functionality
- Test database constraint handling
- Test repository method performance

## Risks and Mitigation
- **Risk**: Performance issues with large datasets
- **Mitigation**: Implement proper indexing and query optimization

## Additional Notes
Repositories must follow the TDD strategy outlined in the project documentation, with test-driven development using real database operations rather than mocks. This ensures full-stack validation from entity through database persistence.
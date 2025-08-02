# User Story: ST-002 - ServiceTitan Sync Log Entity Design

## Story Information
- **Story ID**: ST-002
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 3
- **Priority**: Must Have
- **Component**: Entity Layer

## User Story
**As a** system administrator  
**I want** to track all synchronization attempts and results  
**So that** I can monitor system performance and troubleshoot issues

## Detailed Description
This story creates the entity structure for comprehensive logging of all ServiceTitan synchronization operations. This includes tracking sync attempts, success/failure status, error details, and performance metrics for operational monitoring and troubleshooting.

## Acceptance Criteria
- [ ] Create `ServiceTitanSyncLog` entity for operation tracking
- [ ] Track sync type (manual/scheduled) and data type (invoices/customers/both)
- [ ] Include status tracking (running/completed/failed)
- [ ] Store error messages and processing statistics
- [ ] Associate with `ServiceTitanCredential` for multi-tenant tracking

## Technical Implementation Notes
- **Entity Location**: `src/Module/ServiceTitan/Entity/ServiceTitanSyncLog.php`
- **Database Table**: `servicetitan_sync_logs`
- **Architecture Reference**: Section 4.2

### Required Fields
- `id` (UUID)
- `serviceTitanCredential` (ManyToOne relationship)
- `syncType` (enum: manual, scheduled)
- `dataType` (enum: invoices, customers, both)
- `status` (enum: running, completed, failed)
- `startedAt` (datetime)
- `completedAt` (datetime, nullable)
- `recordsProcessed` (integer, default 0)
- `recordsSuccessful` (integer, default 0)
- `recordsFailed` (integer, default 0)
- `errorMessage` (text, nullable)
- `errorDetails` (json, nullable)
- `processingTimeSeconds` (integer, nullable)

### Relationships
- ManyToOne with ServiceTitanCredential
- Should cascade delete when credential is deleted

## Definition of Done
- [ ] Entity class created with proper relationships
- [ ] Database migration generated
- [ ] Unit tests for entity functionality
- [ ] Proper foreign key constraints
- [ ] Enum values properly validated
- [ ] JSON field handling for error details
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design (must be completed first)

## Testing Requirements
- Unit tests for entity validation
- Unit tests for relationship handling
- Database constraint testing
- JSON field serialization testing
- Enum validation testing

## Risks and Mitigation
- **Risk**: Large volume of log entries affecting performance
- **Mitigation**: Plan for log rotation and archival strategy in future stories

## Additional Notes
This entity provides the foundation for operational monitoring and should include comprehensive error tracking to facilitate troubleshooting during development and production phases.
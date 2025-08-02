# User Story: ST-007 - Database Schema Migration

## Story Information
- **Story ID**: ST-007
- **Epic**: OAuth Infrastructure Foundation
- **Phase**: Phase 1 - OAuth Infrastructure & Credential Management
- **Story Points**: 3
- **Priority**: Must Have
- **Component**: Database Layer

## User Story
**As a** system administrator  
**I want** proper database schema for ServiceTitan entities  
**So that** credential and sync data is properly stored

## Detailed Description
This story creates the database migration files for ServiceTitan credential and sync log entities, including proper constraints, indexes, and foreign key relationships for optimal performance and data integrity.

## Acceptance Criteria
- [ ] Generate migration for `servicetitan_credentials` table
- [ ] Generate migration for `servicetitan_sync_logs` table
- [ ] Include proper foreign key constraints
- [ ] Add database indexes for performance
- [ ] Include check constraints for enum values
- [ ] Ensure migration is reversible

## Technical Implementation Notes
- **Migration Tool**: Use `bin/console doctrine:migrations:diff` after entity creation
- **Cleanup**: Clean up migration file for feature-specific changes only
- **Architecture Reference**: Section 9.1

### servicetitan_credentials Table Structure
```sql
CREATE TABLE servicetitan_credentials (
    id UUID PRIMARY KEY,
    company_id UUID NOT NULL REFERENCES companies(id) ON DELETE CASCADE,
    environment VARCHAR(20) NOT NULL CHECK (environment IN ('integration', 'production')),
    client_id TEXT NOT NULL,
    client_secret TEXT NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    connection_status VARCHAR(20) NOT NULL DEFAULT 'inactive' 
        CHECK (connection_status IN ('active', 'inactive', 'error')),
    last_connection_attempt TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    UNIQUE(company_id, environment)
);
```

### servicetitan_sync_logs Table Structure
```sql
CREATE TABLE servicetitan_sync_logs (
    id UUID PRIMARY KEY,
    servicetitan_credential_id UUID NOT NULL 
        REFERENCES servicetitan_credentials(id) ON DELETE CASCADE,
    sync_type VARCHAR(20) NOT NULL CHECK (sync_type IN ('manual', 'scheduled')),
    data_type VARCHAR(20) NOT NULL CHECK (data_type IN ('invoices', 'customers', 'both')),
    status VARCHAR(20) NOT NULL CHECK (status IN ('running', 'completed', 'failed')),
    started_at TIMESTAMP NOT NULL DEFAULT NOW(),
    completed_at TIMESTAMP,
    records_processed INTEGER DEFAULT 0,
    records_successful INTEGER DEFAULT 0,
    records_failed INTEGER DEFAULT 0,
    error_message TEXT,
    error_details JSON,
    processing_time_seconds INTEGER
);
```

### Required Indexes
```sql
-- Performance indexes for credentials
CREATE INDEX idx_servicetitan_credentials_company ON servicetitan_credentials(company_id);
CREATE INDEX idx_servicetitan_credentials_status ON servicetitan_credentials(connection_status);
CREATE INDEX idx_servicetitan_credentials_expires ON servicetitan_credentials(token_expires_at);

-- Performance indexes for sync logs
CREATE INDEX idx_servicetitan_sync_logs_credential ON servicetitan_sync_logs(servicetitan_credential_id);
CREATE INDEX idx_servicetitan_sync_logs_status ON servicetitan_sync_logs(status);
CREATE INDEX idx_servicetitan_sync_logs_started ON servicetitan_sync_logs(started_at);
CREATE INDEX idx_servicetitan_sync_logs_type ON servicetitan_sync_logs(sync_type, data_type);
```

## Definition of Done
- [ ] Migration files generated and tested
- [ ] Schema properly created in development environment
- [ ] Foreign key constraints working
- [ ] Indexes created for query performance
- [ ] Migration rollback tested
- [ ] Check constraints enforced
- [ ] Unique constraints working
- [ ] All enum values validated
- [ ] Migration cleaned of unrelated changes

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design
- **ST-002**: ServiceTitan Sync Log Entity Design

## Testing Requirements
- Test migration execution in clean database
- Test migration rollback functionality
- Verify all constraints are enforced
- Test foreign key relationships
- Verify index performance improvements
- Test enum constraint validation

## Migration Generation Process
1. Complete entity development and testing
2. Run `bin/console doctrine:migrations:diff`
3. Review generated migration file
4. Clean up any unrelated changes
5. Test migration execution
6. Test migration rollback
7. Commit cleaned migration file

## Risks and Mitigation
- **Risk**: Migration conflicts with existing schema
- **Mitigation**: Thorough testing in development environment
- **Risk**: Performance impact from new indexes
- **Mitigation**: Monitor query performance after deployment

## Additional Notes
Migration files must be cleaned to include only ServiceTitan-specific changes. The migration should be thoroughly tested for both forward and backward compatibility to ensure safe deployment to production environments.
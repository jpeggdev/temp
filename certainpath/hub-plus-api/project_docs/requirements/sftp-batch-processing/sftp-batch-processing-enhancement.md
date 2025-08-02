# SFTP Batch Processing Enhancement Requirements

## Project Overview

**Feature Name:** SFTP Batch Processing with Audit Trail  
**Epic ID:** TBD  
**Priority:** High  
**Estimated Effort:** Medium  

## Problem Statement

The current `UploadPostageExpenseService` processes individual USPS postage files but lacks capability to:
- Batch process multiple files from SFTP directory
- Track processing history and prevent duplicate processing
- Handle SFTP-specific file formats with different column headers
- Provide audit trail for processed files

## Business Goals

1. **Automated SFTP Processing**: Process all files in SFTP directory with single command
2. **Duplicate Prevention**: Avoid reprocessing same files multiple times
3. **Audit Trail**: Maintain complete history of processed files and results
4. **Data Integrity**: Ensure reliable processing with error tracking and recovery

## User Stories

### Epic: SFTP Batch Processing Enhancement

**As a** system administrator  
**I want** to process all SFTP postage files in a directory  
**So that** I can efficiently import postage data without manual file-by-file processing

**As a** data analyst  
**I want** to see a complete audit trail of processed files  
**So that** I can track what data was imported and when

**As a** system operator  
**I want** automatic duplicate prevention  
**So that** I don't accidentally import the same data multiple times

## Functional Requirements

### FR1: Batch Directory Processing
- Process all .txt files in specified directory
- Skip already processed files by default
- Option to reprocess files if content has changed
- Return summary of processing results

### FR2: File-Level Deduplication
- Track processed files by filename and content hash
- Prevent reprocessing identical files
- Allow reprocessing if file content changes
- Configurable skip/reprocess behavior

### FR3: Audit Trail
- Record all file processing attempts
- Track success/failure status with details
- Store metadata: filename, hash, timestamp, record counts
- Maintain processing history for analysis

### FR4: SFTP Column Mapping
- Support SFTP-specific column headers:
  - "Job ID" → reference
  - "Number of Pieces" → quantity_sent  
  - "Transaction Amount" → cost
- Maintain backward compatibility with existing mappings

### FR5: Error Handling & Recovery
- Continue processing other files if one fails
- Record detailed error messages
- Allow selective reprocessing of failed files
- Comprehensive logging for debugging

## Technical Requirements

### TR1: Test-Driven Development
- All new code must be developed using TDD methodology
- Comprehensive test coverage using existing SFTP test files
- Unit tests for all service methods
- Integration tests for end-to-end processing

### TR2: Entity Design
- New `PostageProcessedFile` entity for audit trail
- Enhanced `BatchPostageRecordMap` for SFTP compatibility
- Non-breaking changes to existing entities

### TR3: Service Architecture  
- New `SftpBatchPostageProcessorService` for orchestration
- Reuse existing `UploadPostageExpenseService` logic
- Repository pattern for data access
- Symfony console command for CLI access

### TR4: Data Integrity
- Database transactions for atomic processing
- Rollback capability on critical errors
- Data validation before processing
- Foreign key constraints where applicable

## Constraints & Assumptions

### Constraints
- Must not break existing functionality
- Must reuse existing `UploadPostageExpenseService` logic
- Must follow Symfony best practices and Hub Plus API conventions
- Must pass all existing tests

### Assumptions
- SFTP files follow consistent format shown in test files
- Test directory structure represents production file organization
- Existing `BatchPostage` entity structure is sufficient
- PostgreSQL database can handle additional audit table

## Success Criteria

1. **Functional Success:**
   - ✅ Process entire SFTP directory with single command
   - ✅ Zero duplicate records from reprocessing same files
   - ✅ Complete audit trail of all processing activities
   - ✅ Existing functionality unchanged and tests passing

2. **Technical Success:**
   - ✅ 100% test coverage for new code
   - ✅ All tests passing (existing + new)
   - ✅ PHPStan level 8 compliance
   - ✅ Code follows project style guidelines

3. **Performance Success:**
   - ✅ Process 38 test files in under 30 seconds
   - ✅ Memory usage scales linearly with file count
   - ✅ No database deadlocks during concurrent processing

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|---------|------------|
| Column mapping changes break existing imports | High | Comprehensive backward compatibility testing |
| Large directories cause memory issues | Medium | Stream processing, batch commits |
| Concurrent processing creates database conflicts | Medium | Database transactions, unique constraints |
| Test files don't represent production data | Low | Validate with production samples |

## Dependencies

- Existing `UploadPostageExpenseService` (no changes required)
- Existing `BatchPostageRecordMap` (minor enhancements)
- Existing test infrastructure
- Doctrine ORM for new entity
- Symfony Console for CLI command

## Out of Scope

- Real-time SFTP monitoring/polling
- Email notifications for processing results
- Web UI for file management
- Historical data migration
- Performance optimization beyond basic requirements

## Definition of Done

- [ ] All acceptance criteria met
- [ ] Unit tests written and passing (TDD approach)
- [ ] Integration tests cover end-to-end scenarios
- [ ] Code review completed and approved
- [ ] PHPStan analysis passes without errors
- [ ] Documentation updated
- [ ] Feature deployed to staging environment
- [ ] Stakeholder acceptance testing completed
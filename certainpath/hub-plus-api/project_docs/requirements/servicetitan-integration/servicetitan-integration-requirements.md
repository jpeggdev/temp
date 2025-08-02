# ServiceTitan Integration Requirements

## Document Information
- **Project**: ServiceTitan API Integration for Automated Data Extraction
- **Feature Module**: `App\Module\ServiceTitan` (first-class top-level feature)
- **Documentation Folder**: `servicetitan-integration`
- **Date**: 2025-07-29
- **Status**: Requirements Analysis Phase
- **Priority**: High

---

## 1. Executive Summary

### Business Problem
Currently, ServiceTitan data integration requires manual intervention:
- Manual report generation in ServiceTitan platform
- Manual file uploads to Hub Plus API
- Monthly data refresh cycle with human dependency
- Inconsistent data freshness across client accounts

### Proposed Solution
Implement automated ServiceTitan API integration to eliminate manual processes and enable scheduled data extraction with improved data freshness (weekly vs. monthly).

### Success Metrics
- **Zero manual intervention** for regular data updates
- **Improved data freshness**: Weekly vs. monthly updates
- **Reduced operational overhead**: Eliminate manual report generation/upload
- **Enhanced reliability**: Automated error handling and retry mechanisms

---

## 2. Business Requirements

### 2.1 Core Functional Requirements

#### FR-001: Automated Data Extraction
- **Requirement**: System shall automatically extract customer and invoice data from ServiceTitan via API
- **Priority**: Must Have
- **Acceptance Criteria**:
  - API integration retrieves customer data equivalent to current manual customer reports
  - API integration retrieves invoice data equivalent to current manual invoice reports
  - Data extraction occurs without manual intervention once configured

#### FR-002: Scheduled Data Synchronization
- **Requirement**: System shall support configurable scheduling for automatic data pulls
- **Priority**: Must Have
- **User Story**: As a system administrator, I want to configure automatic data synchronization schedules so that data stays current without manual intervention
- **Acceptance Criteria**:
  - Support weekly, monthly, and custom interval scheduling
  - Allow manual sync triggering for immediate updates
  - Maintain sync history and status tracking
  - Send notifications for successful and failed synchronizations

#### FR-003: Multi-Tenant OAuth Management
- **Requirement**: System shall manage OAuth authentication for multiple ServiceTitan tenant accounts
- **Priority**: Must Have
- **User Story**: As a client administrator, I want to securely connect my ServiceTitan account so that the system can access my data automatically
- **Acceptance Criteria**:
  - Support multiple client credentials (Tenant ID, Client ID, Client Secret)
  - Environment-specific credential management (Integration vs. Production)
  - Connection status tracking per tenant
  - Secure credential storage with encryption

#### FR-004: Data Synchronization Dashboard
- **Requirement**: System shall provide real-time visibility into synchronization status and history
- **Priority**: Should Have
- **User Story**: As a system administrator, I want to monitor data synchronization status so that I can ensure data integrity and troubleshoot issues
- **Acceptance Criteria**:
  - Display current connection status for each tenant
  - Show sync history with timestamps and record counts
  - Provide error logs and failure reasons
  - Allow manual sync triggering per tenant

### 2.2 Non-Functional Requirements

#### NFR-001: Performance
- **Data Processing**: Handle large datasets efficiently using existing batch processing (2000 records/batch)
- **API Rate Limits**: Respect ServiceTitan API rate limits with appropriate throttling
- **Response Time**: Credential management interface shall respond within 2 seconds
- **Throughput**: Process equivalent data volumes as current file-based system

#### NFR-002: Security
- **Credential Protection**: OAuth credentials encrypted at rest and in transit
- **Access Control**: Integration with existing Hub Plus API authentication/authorization
- **Audit Trail**: Complete logging of all API operations and credential access
- **Data Privacy**: Comply with existing data protection standards

#### NFR-003: Reliability
- **Error Handling**: Comprehensive error recovery with retry mechanisms
- **Data Integrity**: Validation equivalent to existing file processing
- **Monitoring**: Real-time alerting for failed synchronizations
- **Rollback**: Ability to handle partial sync failures gracefully

#### NFR-004: Maintainability
- **Code Reuse**: Leverage existing Hub Plus API infrastructure (80% reuse target)
- **Modular Design**: Follow established modular architecture patterns
- **Documentation**: Comprehensive API documentation and integration guides
- **Testing**: Full test coverage following TDD practices

---

## 3. Technical Requirements

### 3.1 ServiceTitan API Integration

#### TR-001: API Client Implementation
- **OAuth 2.0 Authentication**: Implement ServiceTitan OAuth flow
- **API Endpoints**: Integrate with customer and invoice data endpoints
- **Rate Limiting**: Implement proper rate limiting and pagination handling
- **Error Handling**: Comprehensive error recovery with exponential backoff

#### TR-002: Data Mapping and Transformation
- **API Response Mapping**: Convert ServiceTitan API responses to existing record structures
- **Data Validation**: Apply existing validation rules to API-sourced data
- **Format Compatibility**: Ensure API data matches existing database schema
- **Field Mapping**: Create ServiceTitan-specific field mappings extending existing record maps

### 3.2 Architecture Integration

#### TR-003: Existing Infrastructure Leverage
**Priority**: Must Have - maximize reuse of proven components

**Direct Reuse Components**:
- `InvoiceRecord` and `MemberRecord` entities (full reuse)
- `CoreFieldsTrait`, `InvoiceFieldsTrait`, `MemberFieldsTrait` (full reuse)
- `IngestRepository->insertInvoiceRecords()` and `insertInvoiceRecord()` methods (full reuse)
- `CompanyDataImportJobRepository` for progress tracking (full reuse)
- `UnificationCompanyProcessingDispatchService` for downstream processing (full reuse)

**Adapter Pattern Implementation**:
```
ServiceTitan API → ServiceTitanDataProcessor → InvoiceRecord/MemberRecord → IngestRepository → Database
```

**New Components Required**:
- `ServiceTitanClient` (extends `DomainClient`)
- `ServiceTitanIntegrationService` (mirrors `FieldServicesUploadService`)
- `ServiceTitanInvoiceRecordMap` and `ServiceTitanMemberRecordMap` (extend existing maps)
- OAuth credential management service
- ServiceTitan-specific controllers and UI components

#### TR-004: Database Schema Compatibility
- **Existing Tables**: Utilize existing `invoices_stream` and `members_stream` tables
- **No Schema Changes**: API data flows through existing database structure
- **Data Source Tracking**: Add `software` field value of "ServiceTitan" to distinguish data source
- **Tenant Identification**: Use existing tenant identification patterns

### 3.3 OAuth Authentication Architecture

#### TR-005: Multi-Tenant OAuth Management
**Complexity Level**: High - Primary architectural challenge

**ServiceTitan OAuth Requirements**:
1. **App Registration**: Application must be registered in ServiceTitan Developer Portal
2. **Tenant ID Management**: Each client's Tenant ID must be added to the registered app
3. **Client Approval**: Each client must approve the app in ServiceTitan "Integrations > API Application Access"
4. **Environment-Specific Credentials**: Support Integration and Production environments
5. **Credential Storage**: Secure storage for Client ID/Secret pairs per tenant

**Authentication Flow Design**:
```
1. Admin registers app in ServiceTitan Developer Portal
2. Client provides Tenant ID, Client ID, Client Secret via UI
3. System stores encrypted credentials
4. Client approves app in their ServiceTitan interface
5. System performs OAuth handshake to obtain access tokens
6. Automated token refresh and credential management
```

#### TR-006: Credential Management UI
- **Secure Input Forms**: Masked Client Secret fields with proper validation
- **Environment Selection**: Toggle between Integration and Production
- **Connection Status**: Real-time display of connection state per tenant
- **Approval Workflow**: Guide clients through ServiceTitan approval process
- **Credential Testing**: Validate credentials before saving

---

## 4. User Experience Requirements

### 4.1 Administrator Experience

#### UX-001: Initial Setup Workflow
- **ServiceTitan App Registration**: Clear instructions for Developer Portal setup
- **Tenant Onboarding**: Step-by-step client credential collection process
- **Connection Validation**: Immediate feedback on credential validity
- **Setup Completion**: Clear indication when integration is ready for use

#### UX-002: Ongoing Management Interface
- **Dashboard Overview**: At-a-glance status of all ServiceTitan integrations
- **Sync Controls**: Easy access to manual sync triggers and scheduling configuration
- **Error Resolution**: Clear error messages with actionable resolution steps
- **Historical Data**: Access to sync logs and data processing history

### 4.2 Client Experience

#### UX-003: Client Onboarding Process
- **Credential Collection**: Simple, secure form for ServiceTitan credentials
- **Approval Guidance**: Clear instructions for approving app in ServiceTitan
- **Status Feedback**: Real-time updates on connection establishment progress
- **Success Confirmation**: Clear confirmation when integration is active

---

## 5. Integration Specifications

### 5.1 Data Flow Architecture

#### Current State (Manual Process):
```
ServiceTitan Platform → Manual Report Generation → File Download → Manual Upload → Hub Plus Processing
```

#### Future State (Automated API):
```
ServiceTitan API → OAuth Authentication → Data Extraction → Record Processing → Database Storage → Downstream Processing
```

### 5.2 Processing Pipeline Integration

#### Batch Processing Reuse:
- **Record Batching**: Maintain existing 2000-record batch processing
- **Progress Tracking**: Real-time progress updates via existing infrastructure
- **Error Handling**: Skip invalid records with comprehensive logging
- **Data Transformation**: Apply existing customer name processing and field validation

#### Downstream Integration:
- **Unification Processing**: Seamless integration with existing downstream systems
- **Data Format**: API-sourced data indistinguishable from file-sourced data
- **Processing Triggers**: Same processing dispatch mechanisms apply

---

## 6. Security and Compliance

### 6.1 Data Protection
- **Encryption**: OAuth credentials encrypted using existing encryption standards
- **Access Control**: Integration with Hub Plus API role-based access control
- **Audit Logging**: Complete audit trail of all authentication and data operations
- **Data Retention**: Follow existing data retention policies for API-sourced data

### 6.2 API Security
- **Token Management**: Secure OAuth token storage and automatic refresh
- **Rate Limiting**: Respect ServiceTitan API limits to prevent service disruption
- **Error Logging**: Security-conscious error logging without credential exposure
- **Network Security**: HTTPS-only communication with proper certificate validation

---

## 7. Testing Requirements

### 7.1 Test-Driven Development Strategy
Following established Hub Plus API TDD practices:

#### Unit Testing:
- **ServiceTitanClient**: Mock API responses and test error handling
- **Record Processing**: Test data transformation and validation logic  
- **OAuth Management**: Test credential storage and token refresh logic
- **Integration Service**: Test sync processes with mock API data

#### Integration Testing:
- **API Connectivity**: Test against ServiceTitan Integration environment
- **Database Operations**: Validate data flows through existing infrastructure
- **Error Scenarios**: Test failure modes and recovery mechanisms
- **Performance**: Validate batch processing with large datasets

#### End-to-End Testing:
- **Complete Workflow**: Full OAuth setup through data synchronization
- **Multi-Tenant**: Test multiple client configurations simultaneously
- **Scheduling**: Validate automated sync operations
- **UI Workflows**: Test complete user experience flows

### 7.2 Quality Gates
- **PHPStan Analysis**: Zero errors required before commit
- **PHP-CS-Fixer**: Automated code style compliance
- **Test Coverage**: Minimum 90% coverage for new components
- **Integration Tests**: All critical paths must have integration test coverage

---

## 8. Deployment and Operations

### 8.1 Deployment Requirements
- **Environment Configuration**: Support for Integration and Production ServiceTitan environments
- **Credential Migration**: Secure process for moving credentials between environments
- **Feature Flags**: Gradual rollout capability for risk mitigation
- **Rollback Plan**: Ability to revert to manual processing if needed

### 8.2 Monitoring and Alerting
- **Sync Monitoring**: Real-time alerts for failed synchronizations
- **Performance Metrics**: Track API response times and data processing rates
- **Error Rate Monitoring**: Alert on authentication failures or API errors
- **Data Quality Checks**: Validate expected data volumes and formats

### 8.3 Maintenance Operations
- **Credential Rotation**: Support for periodic credential updates
- **API Version Management**: Handle ServiceTitan API versioning changes
- **Data Reconciliation**: Tools to compare API vs. manual data for validation
- **Performance Optimization**: Ongoing monitoring and optimization of batch processing

---

## 9. Dependencies and Constraints

### 9.1 External Dependencies
- **ServiceTitan Developer Portal**: App registration and approval process
- **ServiceTitan API**: Availability and rate limits
- **Client Cooperation**: Manual approval process in ServiceTitan interface
- **Network Connectivity**: Reliable internet connection for API operations

### 9.2 Technical Constraints
- **API Rate Limits**: Must respect ServiceTitan's rate limiting policies
- **Data Format**: API responses must map to existing record structures
- **OAuth Complexity**: Multi-tenant OAuth implementation requires careful design
- **Backward Compatibility**: Must maintain compatibility with existing data processing

### 9.3 Business Constraints
- **Client Onboarding**: Manual setup process required for each client
- **Approval Dependencies**: Client must approve app in ServiceTitan before automation works
- **Environment Management**: Production credentials require separate management
- **Support Overhead**: New support processes needed for OAuth troubleshooting

---

## 10. Success Criteria and Acceptance Testing

### 10.1 MVP Success Criteria
- [ ] **Automated Data Extraction**: Successfully pull customer and invoice data via API
- [ ] **OAuth Implementation**: Multi-tenant credential management working securely
- [ ] **Scheduled Synchronization**: Configurable automation running reliably
- [ ] **Data Integrity**: API-sourced data equivalent to manual reports
- [ ] **Error Handling**: Robust error recovery and notification system
- [ ] **User Interface**: Complete client onboarding and management workflows

### 10.2 Performance Benchmarks
- **Data Processing**: Handle same data volumes as existing file processing
- **API Response Time**: 95% of API calls complete within 5 seconds
- **Batch Processing**: Maintain existing 2000-record batch efficiency
- **Error Rate**: Less than 1% failure rate for properly configured integrations

### 10.3 User Acceptance Criteria
- **Administrator Experience**: Can set up new ServiceTitan integration in under 30 minutes
- **Client Experience**: Clear guidance through approval process with success confirmation
- **Operational Experience**: Dashboard provides clear visibility into all integration status
- **Support Experience**: Clear error messages enable rapid troubleshooting

---

## 11. Future Enhancements (Post-MVP)

### 11.1 Advanced Features
- **Real-time Webhooks**: ServiceTitan webhook integration for immediate data updates
- **Advanced Scheduling**: Custom scheduling with business rules (e.g., avoid weekends)
- **Data Transformation Rules**: Client-specific data mapping and transformation
- **Bulk Operations**: Mass client onboarding and management tools

### 11.2 Integration Expansions
- **Additional ServiceTitan Endpoints**: Expand beyond customer and invoice data
- **Enhanced Reporting**: Custom reports using integrated ServiceTitan data
- **Workflow Automation**: Trigger business processes based on ServiceTitan data changes
- **Multi-Platform Support**: Template for integrating additional field service platforms

---

## 12. Risk Assessment and Mitigation

### 12.1 High-Risk Areas

#### OAuth Implementation Complexity
- **Risk**: Multi-tenant OAuth management is complex and error-prone
- **Impact**: High - Core functionality depends on authentication
- **Mitigation**: Extensive testing with ServiceTitan Integration environment, phased rollout

#### ServiceTitan API Dependencies
- **Risk**: API changes or outages could break integration
- **Impact**: Medium - Clients can revert to manual process
- **Mitigation**: Version pinning, comprehensive error handling, fallback procedures

#### Client Onboarding Complexity
- **Risk**: Manual approval process may cause adoption challenges
- **Impact**: Medium - Slows rollout but doesn't prevent functionality
- **Mitigation**: Clear documentation, support processes, video guides

### 12.2 Technical Risks

#### Data Mapping Accuracy
- **Risk**: API data structure differences could cause data quality issues
- **Impact**: High - Incorrect data affects business operations
- **Mitigation**: Comprehensive validation, comparison testing with manual reports

#### Performance at Scale
- **Risk**: Large datasets could overwhelm API or processing capabilities
- **Impact**: Medium - Affects user experience but not data integrity
- **Mitigation**: Load testing, pagination optimization, monitoring

---

## 13. Appendices

### Appendix A: ServiceTitan API Reference
*[To be populated with specific API endpoint documentation]*

### Appendix B: Existing Architecture Patterns
*[Reference to similar integrations in Hub Plus API codebase]*

### Appendix C: OAuth Flow Diagrams
*[Detailed technical flow diagrams for authentication process]*

### Appendix D: Error Code Reference
*[Comprehensive error codes and resolution procedures]*

---

**Document Status**: ✅ Complete - Ready for Architectural Design Phase
**Next Phase**: Technical Architecture Design and OAuth Implementation Planning
**Approval Required**: Product Owner and Technical Lead sign-off
# User Story: ST-018 - Security Voters Implementation

## Story Information
- **Story ID**: ST-018
- **Epic**: User Experience & Management Interface
- **Phase**: Phase 3 - UI Components & Synchronization Services
- **Story Points**: 3
- **Priority**: Must Have
- **Component**: Security Layer

## User Story
**As a** security administrator  
**I want** proper access control for ServiceTitan operations  
**So that** only authorized users can manage credentials and trigger syncs

## Detailed Description
This story implements Symfony security voters for comprehensive access control across all ServiceTitan operations. The voters ensure proper authorization based on user roles, company membership, and specific permissions while maintaining audit trails for security decisions.

## Acceptance Criteria
- [ ] Create security voters for credential management
- [ ] Implement access control for sync operations
- [ ] Support company-level access restrictions
- [ ] Add role-based permissions for different operations
- [ ] Include audit logging for security decisions
- [ ] Support read-only access for monitoring

## Technical Implementation Notes
- **Voter Location**: `src/Module/ServiceTitan/Feature/*/Voter/`
- **Pattern**: Follow existing Hub Plus API voter patterns
- **Architecture Reference**: Section 8.2

### Core Security Voters

#### ServiceTitanCredentialVoter
```php
class ServiceTitanCredentialVoter extends Voter
{
    public const CREATE = 'CREATE_SERVICETITAN_CREDENTIAL';
    public const VIEW = 'VIEW_SERVICETITAN_CREDENTIAL';
    public const EDIT = 'EDIT_SERVICETITAN_CREDENTIAL';
    public const DELETE = 'DELETE_SERVICETITAN_CREDENTIAL';
    public const TEST = 'TEST_SERVICETITAN_CREDENTIAL';
    public const LIST = 'LIST_SERVICETITAN_CREDENTIALS';
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::CREATE, self::VIEW, self::EDIT, self::DELETE, self::TEST, self::LIST])) {
            return false;
        }
        
        // For CREATE and LIST, subject is Company
        if (in_array($attribute, [self::CREATE, self::LIST])) {
            return $subject instanceof Company;
        }
        
        // For other operations, subject is ServiceTitanCredential
        return $subject instanceof ServiceTitanCredential;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return false;
        }
        
        return match($attribute) {
            self::CREATE => $this->canCreate($user, $subject),
            self::VIEW => $this->canView($user, $subject),
            self::EDIT => $this->canEdit($user, $subject),
            self::DELETE => $this->canDelete($user, $subject),
            self::TEST => $this->canTest($user, $subject),
            self::LIST => $this->canList($user, $subject),
            default => false,
        };
    }
    
    private function canCreate(User $user, Company $company): bool
    {
        // Must be employee of the company with servicetitan_admin role
        $employee = $user->getEmployeeForCompany($company);
        
        if (!$employee) {
            $this->auditLogger->logAccessDenied('servicetitan_create', $user, $company, 'Not an employee');
            return false;
        }
        
        if (!$employee->hasPermission('servicetitan_admin')) {
            $this->auditLogger->logAccessDenied('servicetitan_create', $user, $company, 'Missing servicetitan_admin permission');
            return false;
        }
        
        $this->auditLogger->logAccessGranted('servicetitan_create', $user, $company);
        return true;
    }
    
    private function canView(User $user, ServiceTitanCredential $credential): bool
    {
        $employee = $user->getEmployeeForCompany($credential->getCompany());
        
        if (!$employee) {
            return false;
        }
        
        // Can view if has servicetitan_admin or servicetitan_view permission
        return $employee->hasPermission('servicetitan_admin') || 
               $employee->hasPermission('servicetitan_view');
    }
    
    private function canEdit(User $user, ServiceTitanCredential $credential): bool
    {
        $employee = $user->getEmployeeForCompany($credential->getCompany());
        
        if (!$employee) {
            return false;
        }
        
        return $employee->hasPermission('servicetitan_admin');
    }
    
    private function canDelete(User $user, ServiceTitanCredential $credential): bool
    {
        // Only servicetitan_admin can delete, and only if no recent syncs
        $employee = $user->getEmployeeForCompany($credential->getCompany());
        
        if (!$employee || !$employee->hasPermission('servicetitan_admin')) {
            return false;
        }
        
        // Additional business rule: can't delete if sync in last 24 hours
        if ($this->hasRecentSync($credential)) {
            $this->auditLogger->logAccessDenied('servicetitan_delete', $user, $credential, 'Recent sync activity');
            return false;
        }
        
        return true;
    }
}
```

#### ServiceTitanSyncVoter
```php
class ServiceTitanSyncVoter extends Voter
{
    public const TRIGGER = 'TRIGGER_SERVICETITAN_SYNC';
    public const CANCEL = 'CANCEL_SERVICETITAN_SYNC';
    public const VIEW_STATUS = 'VIEW_SERVICETITAN_SYNC';
    public const VIEW_HISTORY = 'VIEW_SERVICETITAN_SYNC';
    public const VIEW_METRICS = 'VIEW_SERVICETITAN_METRICS';
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        $supportedAttributes = [
            self::TRIGGER, self::CANCEL, self::VIEW_STATUS, 
            self::VIEW_HISTORY, self::VIEW_METRICS
        ];
        
        return in_array($attribute, $supportedAttributes) && $subject instanceof ServiceTitanCredential;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return false;
        }
        
        return match($attribute) {
            self::TRIGGER => $this->canTriggerSync($user, $subject),
            self::CANCEL => $this->canCancelSync($user, $subject),
            self::VIEW_STATUS, self::VIEW_HISTORY, self::VIEW_METRICS => $this->canViewSync($user, $subject),
            default => false,
        };
    }
    
    private function canTriggerSync(User $user, ServiceTitanCredential $credential): bool
    {
        $employee = $user->getEmployeeForCompany($credential->getCompany());
        
        if (!$employee) {
            return false;
        }
        
        // Can trigger if has servicetitan_admin or servicetitan_sync permission
        if (!($employee->hasPermission('servicetitan_admin') || $employee->hasPermission('servicetitan_sync'))) {
            return false;
        }
        
        // Additional business rule: can't trigger if sync already running
        if ($this->hasSyncRunning($credential)) {
            $this->auditLogger->logAccessDenied('servicetitan_trigger_sync', $user, $credential, 'Sync already running');
            return false;
        }
        
        return true;
    }
    
    private function canCancelSync(User $user, ServiceTitanCredential $credential): bool
    {
        $employee = $user->getEmployeeForCompany($credential->getCompany());
        
        if (!$employee) {
            return false;
        }
        
        // Only servicetitan_admin can cancel syncs
        return $employee->hasPermission('servicetitan_admin');
    }
    
    private function canViewSync(User $user, ServiceTitanCredential $credential): bool
    {
        $employee = $user->getEmployeeForCompany($credential->getCompany());
        
        if (!$employee) {
            return false;
        }
        
        // Can view if has any servicetitan permission
        return $employee->hasPermission('servicetitan_admin') || 
               $employee->hasPermission('servicetitan_view') ||
               $employee->hasPermission('servicetitan_sync');
    }
}
```

#### ServiceTitanDashboardVoter
```php
class ServiceTitanDashboardVoter extends Voter
{
    public const VIEW_DASHBOARD = 'VIEW_SERVICETITAN_DASHBOARD';
    
    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW_DASHBOARD && $subject instanceof Company;
    }
    
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        
        if (!$user instanceof User) {
            return false;
        }
        
        $employee = $user->getEmployeeForCompany($subject);
        
        if (!$employee) {
            return false;
        }
        
        // Can view dashboard if has any servicetitan permission
        return $employee->hasPermission('servicetitan_admin') || 
               $employee->hasPermission('servicetitan_view') ||
               $employee->hasPermission('servicetitan_sync');
    }
}
```

## Definition of Done
- [ ] Security voters implemented for all operations
- [ ] Access control working correctly
- [ ] Role-based permissions enforced
- [ ] Audit logging integrated
- [ ] Unit tests for security scenarios passing
- [ ] Integration tests with controllers verified
- [ ] Business rules properly enforced
- [ ] Cross-company access prevention verified
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-001**: ServiceTitan Credential Entity Design
- **ST-015**: Credential Management Controllers
- **ST-016**: Data Synchronization Controllers

## Testing Requirements
- Unit tests for each voter with various permission scenarios
- Unit tests for business rule enforcement
- Integration tests with controllers
- Test cross-company access prevention
- Test audit logging functionality

### Security Test Examples
```php
class ServiceTitanCredentialVoterTest extends TestCase
{
    public function testCanCreateWithAdminPermission(): void
    {
        $user = $this->createUserWithPermission('servicetitan_admin');
        $company = $user->getCurrentCompany();
        
        $voter = new ServiceTitanCredentialVoter($this->auditLogger);
        
        $result = $voter->vote(
            $this->createToken($user),
            $company,
            [ServiceTitanCredentialVoter::CREATE]
        );
        
        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
    
    public function testCannotCreateWithoutPermission(): void
    {
        $user = $this->createUserWithoutServiceTitanPermissions();
        $company = $user->getCurrentCompany();
        
        $voter = new ServiceTitanCredentialVoter($this->auditLogger);
        
        $result = $voter->vote(
            $this->createToken($user),
            $company,
            [ServiceTitanCredentialVoter::CREATE]
        );
        
        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
    
    public function testCrossCompanyAccessDenied(): void
    {
        $user = $this->createUserWithPermission('servicetitan_admin');
        $otherCompanyCredential = $this->createCredentialForOtherCompany();
        
        $voter = new ServiceTitanCredentialVoter($this->auditLogger);
        
        $result = $voter->vote(
            $this->createToken($user),
            $otherCompanyCredential,
            [ServiceTitanCredentialVoter::VIEW]
        );
        
        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }
}
```

### Business Rule Tests
```php
public function testCannotDeleteCredentialWithRecentSync(): void
{
    $user = $this->createUserWithPermission('servicetitan_admin');
    $credential = $this->createCredentialWithRecentSync();
    
    $voter = new ServiceTitanCredentialVoter($this->auditLogger);
    
    $result = $voter->vote(
        $this->createToken($user),
        $credential,
        [ServiceTitanCredentialVoter::DELETE]
    );
    
    self::assertSame(VoterInterface::ACCESS_DENIED, $result);
}

public function testCannotTriggerSyncWhenSyncRunning(): void
{
    $user = $this->createUserWithPermission('servicetitan_sync');
    $credential = $this->createCredentialWithRunningSynс();
    
    $voter = new ServiceTitanSyncVoter($this->auditLogger, $this->syncLogRepository);
    
    $result = $voter->vote(
        $this->createToken($user),
        $credential,
        [ServiceTitanSyncVoter::TRIGGER]
    );
    
    self::assertSame(VoterInterface::ACCESS_DENIED, $result);
}
```

## Permission Structure
The ServiceTitan integration uses these permissions:
- **servicetitan_admin**: Full access to all operations
- **servicetitan_sync**: Can trigger and view syncs, but cannot manage credentials  
- **servicetitan_view**: Read-only access to credentials and sync status

## Audit Logging Integration
```php
class ServiceTitanAuditLogger
{
    public function logAccessGranted(string $operation, User $user, object $subject): void
    {
        $this->logger->info('ServiceTitan access granted', [
            'operation' => $operation,
            'user_id' => $user->getId(),
            'subject_type' => get_class($subject),
            'subject_id' => method_exists($subject, 'getId') ? $subject->getId() : null,
            'company_id' => $this->extractCompanyId($subject)
        ]);
    }
    
    public function logAccessDenied(string $operation, User $user, object $subject, string $reason): void
    {
        $this->logger->warning('ServiceTitan access denied', [
            'operation' => $operation,
            'user_id' => $user->getId(),
            'subject_type' => get_class($subject),
            'subject_id' => method_exists($subject, 'getId') ? $subject->getId() : null,
            'company_id' => $this->extractCompanyId($subject),
            'reason' => $reason
        ]);
    }
}
```

## Risks and Mitigation
- **Risk**: Overly restrictive permissions blocking legitimate access
- **Mitigation**: Comprehensive testing with various user roles and scenarios
- **Risk**: Security bypass through insufficient validation
- **Mitigation**: Thorough unit testing and integration testing of all voter logic

## Additional Notes
These security voters are critical for maintaining proper access control in the ServiceTitan integration. They must be thoroughly tested to ensure no unauthorized access while providing appropriate functionality to legitimate users.
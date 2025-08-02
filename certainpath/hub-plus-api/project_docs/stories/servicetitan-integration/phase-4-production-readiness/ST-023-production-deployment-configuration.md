# User Story: ST-023 - Production Deployment Configuration

## Story Information
- **Story ID**: ST-023
- **Epic**: Production Readiness & Operations
- **Phase**: Phase 4 - Testing, Monitoring & Production Deployment
- **Story Points**: 3
- **Priority**: Must Have
- **Component**: Configuration Layer

## User Story
**As a** system administrator  
**I want** production-ready configuration and deployment setup  
**So that** ServiceTitan integration can be safely deployed to production

## Detailed Description
This story creates all necessary configuration, deployment scripts, documentation, and operational procedures for safely deploying the ServiceTitan integration to production. This includes environment-specific configurations, feature flags, monitoring setup, and rollback procedures.

## Acceptance Criteria
- [ ] Create environment-specific configuration
- [ ] Add feature flags for gradual rollout
- [ ] Include deployment migration scripts
- [ ] Add rollback procedures and documentation
- [ ] Include production monitoring configuration
- [ ] Add security configuration review

## Technical Implementation Notes
- **Configuration Location**: `config/packages/servicetitan.yaml`
- **Architecture Reference**: Section 15

### Environment-Specific Configuration

#### ServiceTitan Configuration (config/packages/servicetitan.yaml)
```yaml
servicetitan:
    # Environment-specific API settings
    api:
        integration:
            base_url: 'https://api-integration.servicetitan.io'
            timeout: 30
            rate_limit: 120 # requests per minute
            retry_attempts: 3
            connect_timeout: 10
        production:
            base_url: 'https://api.servicetitan.io'
            timeout: 30
            rate_limit: 120 # requests per minute
            retry_attempts: 3
            connect_timeout: 10
    
    # Sync configuration
    sync:
        default_batch_size: 2000
        max_concurrent_syncs: 3
        sync_timeout_seconds: 3600
        incremental_lookback_hours: 24
        
    # Error handling configuration
    error_handling:
        retry:
            base_delay_seconds: 1
            max_delay_seconds: 60
            max_attempts: 3
        circuit_breaker:
            failure_threshold: 5
            reset_timeout_seconds: 300
            state_ttl_seconds: 900
            
    # Monitoring configuration
    monitoring:
        metrics_enabled: true
        health_check_enabled: true
        alert_on_sync_failures: true
        alert_failure_threshold: 3
        alert_time_window_minutes: 60
        
    # Security configuration
    security:
        encryption_key: '%env(SERVICETITAN_ENCRYPTION_KEY)%'
        token_refresh_threshold_minutes: 30
        credential_audit_logging: true

when@prod:
    servicetitan:
        # Production-specific overrides
        sync:
            max_concurrent_syncs: 5 # Higher concurrency in production
        monitoring:
            detailed_metrics: true
            performance_tracking: true
        security:
            strict_validation: true
            enhanced_logging: true

when@dev:
    servicetitan:
        # Development-specific overrides
        api:
            timeout: 60 # Longer timeout for debugging
        monitoring:
            detailed_metrics: true
            debug_logging: true
        security:
            strict_validation: false # Allow test data
```

#### Feature Flags Configuration
```yaml
# config/packages/feature_flags.yaml
feature_flags:
    servicetitan_integration:
        enabled: '%env(bool:SERVICETITAN_INTEGRATION_ENABLED)%'
        rollout_percentage: '%env(int:SERVICETITAN_ROLLOUT_PERCENTAGE)%'
        
    servicetitan_scheduled_sync:
        enabled: '%env(bool:SERVICETITAN_SCHEDULED_SYNC_ENABLED)%'
        
    servicetitan_automatic_retry:
        enabled: '%env(bool:SERVICETITAN_AUTO_RETRY_ENABLED)%'
        
    servicetitan_circuit_breaker:
        enabled: '%env(bool:SERVICETITAN_CIRCUIT_BREAKER_ENABLED)%'
        
    servicetitan_enhanced_monitoring:
        enabled: '%env(bool:SERVICETITAN_ENHANCED_MONITORING_ENABLED)%'
```

#### Environment Variables (.env.prod)
```bash
# ServiceTitan Integration Configuration
SERVICETITAN_INTEGRATION_ENABLED=true
SERVICETITAN_ROLLOUT_PERCENTAGE=100
SERVICETITAN_SCHEDULED_SYNC_ENABLED=true
SERVICETITAN_AUTO_RETRY_ENABLED=true
SERVICETITAN_CIRCUIT_BREAKER_ENABLED=true
SERVICETITAN_ENHANCED_MONITORING_ENABLED=true

# Security
SERVICETITAN_ENCRYPTION_KEY=base64:generated-secure-256-bit-key

# API Configuration
SERVICETITAN_DEFAULT_ENVIRONMENT=production
SERVICETITAN_API_TIMEOUT=30
SERVICETITAN_RATE_LIMIT=120

# Monitoring
SERVICETITAN_METRICS_ENABLED=true
SERVICETITAN_ALERT_THRESHOLD=3
```

### Database Migration Scripts

#### Production Migration Checklist (migrations/servicetitan-production-checklist.md)
```markdown
# ServiceTitan Production Migration Checklist

## Pre-Deployment Checks
- [ ] All integration tests passing
- [ ] Performance tests completed
- [ ] Security review completed
- [ ] Database backup completed
- [ ] Rollback plan documented and tested

## Database Changes
- [ ] Run migration: `DoctrineMigrations\Version20250729120000` (ServiceTitan credentials table)
- [ ] Run migration: `DoctrineMigrations\Version20250729120001` (ServiceTitan sync logs table)
- [ ] Verify indexes created for performance
- [ ] Verify foreign key constraints working

## Configuration Changes
- [ ] Environment variables configured
- [ ] Feature flags set appropriately
- [ ] Monitoring alerts configured
- [ ] Encryption keys generated and secured

## Post-Deployment Verification
- [ ] Health check endpoint responding
- [ ] Test credential creation working
- [ ] Test sync operation working
- [ ] Monitoring metrics being collected
- [ ] Alerts firing correctly
```

#### Migration Execution Script (bin/deploy-servicetitan.sh)
```bash
#!/bin/bash

set -e

echo "Starting ServiceTitan integration deployment..."

# Backup database
echo "Creating database backup..."
pg_dump $DATABASE_URL > "backup_$(date +%Y%m%d_%H%M%S).sql"

# Run migrations
echo "Running database migrations..."
bin/console doctrine:migrations:migrate --env=prod --no-interaction

# Clear cache
echo "Clearing application cache..."
bin/console cache:clear --env=prod

# Warm up cache
echo "Warming up cache..."
bin/console cache:warmup --env=prod

# Verify installation
echo "Verifying installation..."
bin/console servicetitan:health-check --env=prod

# Test basic functionality
echo "Testing basic functionality..."
bin/console servicetitan:test-configuration --env=prod

echo "Deployment completed successfully!"
```

### Feature Flag Implementation

#### ServiceTitanFeatureFlag Service
```php
class ServiceTitanFeatureFlagService
{
    public function __construct(
        private readonly FeatureFlagInterface $featureFlags,
        private readonly LoggerInterface $logger
    ) {}
    
    public function isIntegrationEnabled(): bool
    {
        return $this->featureFlags->isEnabled('servicetitan_integration');
    }
    
    public function isScheduledSyncEnabled(): bool
    {
        return $this->featureFlags->isEnabled('servicetitan_scheduled_sync');
    }
    
    public function isEnabledForCompany(Company $company): bool
    {
        if (!$this->isIntegrationEnabled()) {
            return false;
        }
        
        $rolloutPercentage = $this->featureFlags->getPercentage('servicetitan_integration');
        
        if ($rolloutPercentage >= 100) {
            return true;
        }
        
        // Use company ID hash for consistent rollout
        $hash = crc32($company->getId()->toString());
        $percentage = abs($hash) % 100;
        
        $enabled = $percentage < $rolloutPercentage;
        
        if (!$enabled) {
            $this->logger->info('ServiceTitan integration disabled for company due to rollout percentage', [
                'company_id' => $company->getId()->toString(),
                'rollout_percentage' => $rolloutPercentage,
                'company_percentage' => $percentage
            ]);
        }
        
        return $enabled;
    }
    
    public function shouldUseCircuitBreaker(): bool
    {
        return $this->featureFlags->isEnabled('servicetitan_circuit_breaker');
    }
    
    public function shouldAutoRetry(): bool
    {
        return $this->featureFlags->isEnabled('servicetitan_automatic_retry');
    }
}
```

#### Integration with Controllers
```php
// In ServiceTitan controllers
class CreateServiceTitanCredentialController extends AbstractController
{
    public function __invoke(
        CreateServiceTitanCredentialRequest $request,
        ServiceTitanFeatureFlagService $featureFlags
    ): JsonResponse {
        $company = $this->getUser()->getCurrentCompany();
        
        if (!$featureFlags->isEnabledForCompany($company)) {
            return $this->json([
                'error' => 'ServiceTitan integration is not enabled for your company'
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Continue with normal processing...
    }
}
```

### Production Monitoring Configuration

#### Monitoring Services Configuration
```yaml
# config/packages/monitoring.yaml
when@prod:
    monolog:
        handlers:
            servicetitan:
                type: stream
                path: "%kernel.logs_dir%/servicetitan.log"
                level: info
                channels: ["servicetitan"]
                formatter: json
                
            servicetitan_error:
                type: stream
                path: "%kernel.logs_dir%/servicetitan_errors.log"
                level: error
                channels: ["servicetitan"]
                formatter: json
    
    framework:
        cache:
            pools:
                servicetitan.circuit_breaker:
                    adapter: cache.adapter.redis
                    default_lifetime: 900
                    
    services:
        App\Module\ServiceTitan\Service\ServiceTitanHealthCheck:
            tags:
                - { name: 'health_check', alias: 'servicetitan' }
```

#### Health Check Endpoint Configuration
```php
// config/routes.yaml
servicetitan_health:
    path: /health/servicetitan
    controller: App\Module\ServiceTitan\Controller\ServiceTitanHealthController
    methods: [GET]
```

### Rollback Procedures

#### Rollback Script (bin/rollback-servicetitan.sh)
```bash
#!/bin/bash

set -e

BACKUP_FILE=${1:-"latest"}

echo "Starting ServiceTitan integration rollback..."

# Disable feature flags immediately
echo "Disabling ServiceTitan feature flags..."
bin/console feature-flag:disable servicetitan_integration --env=prod
bin/console feature-flag:disable servicetitan_scheduled_sync --env=prod

# Stop any running sync processes
echo "Stopping running sync processes..."
bin/console servicetitan:stop-all-syncs --env=prod

# Rollback database if needed
if [ "$BACKUP_FILE" != "skip" ]; then
    echo "Rolling back database to backup: $BACKUP_FILE"
    # Database rollback commands would go here
    # This would typically involve restoring from backup
fi

# Clear cache
echo "Clearing cache..."
bin/console cache:clear --env=prod

# Verify rollback
echo "Verifying rollback..."
bin/console health:check --env=prod

echo "Rollback completed successfully!"
```

#### Rollback Documentation (docs/servicetitan-rollback.md)
```markdown
# ServiceTitan Integration Rollback Procedures

## When to Rollback
- Critical production issues affecting data integrity
- Performance degradation affecting overall system
- Security vulnerabilities discovered
- Unrecoverable errors in sync operations

## Immediate Actions (< 5 minutes)
1. Disable feature flags:
   ```bash
   bin/console feature-flag:disable servicetitan_integration --env=prod
   ```

2. Stop scheduled syncs:
   ```bash
   bin/console servicetitan:stop-all-syncs --env=prod
   ```

## Full Rollback (< 30 minutes)
1. Execute rollback script:
   ```bash
   bin/rollback-servicetitan.sh [backup-file]
   ```

2. Verify system health:
   ```bash
   bin/console health:check --env=prod
   ```

3. Notify stakeholders

## Recovery Planning
- Document root cause
- Plan fix implementation
- Schedule re-deployment
- Update rollback procedures based on lessons learned
```

### Security Configuration Review

#### Security Checklist (docs/servicetitan-security-checklist.md)
```markdown
# ServiceTitan Security Configuration Checklist

## Encryption
- [ ] Encryption keys generated with sufficient entropy (256-bit)
- [ ] Keys stored securely in environment variables
- [ ] Keys not committed to version control
- [ ] Key rotation procedure documented

## Access Control
- [ ] Security voters implemented for all operations
- [ ] Multi-tenant isolation verified
- [ ] Permission system properly configured
- [ ] Audit logging enabled for all operations

## API Security
- [ ] OAuth tokens encrypted at rest
- [ ] Token refresh implemented
- [ ] API rate limiting configured
- [ ] Circuit breaker prevents abuse

## Network Security
- [ ] HTTPS required for all API calls
- [ ] Certificate validation enabled
- [ ] IP whitelisting configured if required
- [ ] VPN access documented

## Monitoring
- [ ] Security events logged
- [ ] Failed authentication attempts monitored
- [ ] Unusual activity alerts configured
- [ ] Access patterns monitored
```

## Definition of Done
- [ ] Environment configurations complete and tested
- [ ] Feature flags implemented and documented
- [ ] Migration scripts created and tested
- [ ] Rollback procedures documented and tested
- [ ] Monitoring configured and verified
- [ ] Security review completed and documented
- [ ] Deployment scripts tested in staging
- [ ] Production readiness checklist completed
- [ ] Team training on operational procedures completed

## Dependencies
- **All previous stories** (ST-001 through ST-022)

## Testing Requirements
- Test deployment scripts in staging environment
- Test rollback procedures with backup data
- Verify feature flags work correctly
- Test monitoring and alerting
- Security penetration testing

### Deployment Test Script
```php
class ServiceTitanDeploymentTest extends AbstractKernelTestCase
{
    public function testProductionConfigurationLoads(): void
    {
        // Test that production configuration loads without errors
        $container = self::getContainer();
        
        self::assertTrue($container->hasParameter('servicetitan.api.production.base_url'));
        self::assertTrue($container->hasParameter('servicetitan.monitoring.metrics_enabled'));
        
        $apiConfig = $container->getParameter('servicetitan.api.production');
        self::assertArrayHasKey('base_url', $apiConfig);
        self::assertArrayHasKey('timeout', $apiConfig);
        self::assertArrayHasKey('rate_limit', $apiConfig);
    }
    
    public function testFeatureFlagsWork(): void
    {
        $featureFlagService = $this->getService(ServiceTitanFeatureFlagService::class);
        
        // Test feature flag functionality
        self::assertIsBool($featureFlagService->isIntegrationEnabled());
        self::assertIsBool($featureFlagService->isScheduledSyncEnabled());
    }
    
    public function testHealthCheckWorks(): void
    {
        $healthCheck = $this->getService(ServiceTitanHealthCheck::class);
        
        $result = $healthCheck->check();
        
        self::assertInstanceOf(HealthCheckResult::class, $result);
        self::assertContains($result->getStatus(), ['healthy', 'degraded', 'unhealthy']);
    }
}
```

## Gradual Rollout Strategy

### Phase 1: Internal Testing (1 week)
- Enable for internal company accounts only
- Monitor metrics and performance
- Validate functionality with real data

### Phase 2: Beta Customers (2 weeks)
- Enable for 5-10 selected customers
- Close monitoring and support
- Gather feedback and metrics

### Phase 3: Limited Production (4 weeks)
- Roll out to 25% of eligible customers
- Monitor performance and error rates
- Scale up monitoring and support

### Phase 4: Full Production (2 weeks)
- Roll out to all customers
- Continue monitoring
- Prepare for ongoing operations

## Risks and Mitigation
- **Risk**: Configuration errors causing production issues
- **Mitigation**: Comprehensive testing in staging environment matching production
- **Risk**: Rollback procedures not working when needed
- **Mitigation**: Regular testing of rollback procedures in staging

## Additional Notes
This production deployment configuration provides a comprehensive foundation for safely deploying the ServiceTitan integration to production. The configuration should be thoroughly tested and reviewed before deployment to ensure reliability and security.
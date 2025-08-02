# User Story: ST-019 - Scheduled Synchronization Commands

## Story Information
- **Story ID**: ST-019
- **Epic**: Production Readiness & Operations
- **Phase**: Phase 4 - Testing, Monitoring & Production Deployment
- **Story Points**: 5
- **Priority**: Must Have
- **Component**: Command Layer

## User Story
**As a** system administrator  
**I want** console commands for automated data synchronization  
**So that** ServiceTitan data can be updated on configurable schedules

## Detailed Description
This story creates Symfony console commands for automating ServiceTitan data synchronization on configurable schedules. The commands support credential-specific filtering, environment-specific execution, comprehensive logging, and integration with cron scheduling systems.

## Acceptance Criteria
- [ ] Create console command for processing scheduled syncs
- [ ] Support credential-specific and environment-specific filtering
- [ ] Include comprehensive error handling and logging
- [ ] Add progress reporting and status updates
- [ ] Support both individual and batch processing
- [ ] Include dry-run mode for testing

## Technical Implementation Notes
- **Command Location**: `src/Module/ServiceTitan/Feature/DataSynchronization/Command/`
- **Pattern**: Follow existing Hub Plus API command patterns
- **Architecture Reference**: Section 10.1

### Core Console Commands

#### ProcessServiceTitanSyncsCommand
```php
// bin/console servicetitan:process-scheduled-syncs
class ProcessServiceTitanSyncsCommand extends Command
{
    protected static $defaultName = 'servicetitan:process-scheduled-syncs';
    protected static $defaultDescription = 'Process scheduled ServiceTitan data synchronizations';
    
    public function __construct(
        private readonly ServiceTitanIntegrationService $integrationService,
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addOption('environment', 'e', InputOption::VALUE_OPTIONAL, 'Filter by environment (integration|production)')
            ->addOption('company-id', 'c', InputOption::VALUE_OPTIONAL, 'Process only specific company')
            ->addOption('credential-id', null, InputOption::VALUE_OPTIONAL, 'Process only specific credential')
            ->addOption('data-type', 'd', InputOption::VALUE_OPTIONAL, 'Data type to sync (customers|invoices|both)', 'both')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be synced without executing')
            ->addOption('max-concurrent', null, InputOption::VALUE_OPTIONAL, 'Maximum concurrent syncs', '3')
            ->addOption('timeout', 't', InputOption::VALUE_OPTIONAL, 'Timeout per sync in seconds', '3600');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        try {
            $filters = $this->buildFilters($input);
            $credentials = $this->credentialRepository->findScheduledSyncCredentials($filters);
            
            if (empty($credentials)) {
                $io->info('No credentials found matching the criteria');
                return Command::SUCCESS;
            }
            
            $io->note(sprintf('Found %d credentials to process', count($credentials)));
            
            if ($input->getOption('dry-run')) {
                return $this->performDryRun($credentials, $io);
            }
            
            return $this->processCredentials($credentials, $input, $io);
            
        } catch (\Exception $e) {
            $this->logger->error('Scheduled sync command failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $io->error('Command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function processCredentials(array $credentials, InputInterface $input, SymfonyStyle $io): int
    {
        $maxConcurrent = (int) $input->getOption('max-concurrent');
        $timeout = (int) $input->getOption('timeout');
        $dataType = $input->getOption('data-type');
        
        $results = [];
        $semaphore = new \SplObjectStorage();
        
        foreach (array_chunk($credentials, $maxConcurrent) as $batch) {
            $processes = [];
            
            foreach ($batch as $credential) {
                $config = new SyncConfiguration(
                    dataType: $dataType,
                    syncType: 'scheduled',
                    incrementalOnly: true
                );
                
                try {
                    $io->text(sprintf('Starting sync for %s (%s)', 
                        $credential->getCompany()->getName(),
                        $credential->getEnvironment()
                    ));
                    
                    $result = $this->integrationService->synchronizeData($credential, $config);
                    $results[] = $result;
                    
                    $io->success(sprintf('Completed sync for %s: %d records processed',
                        $credential->getCompany()->getName(),
                        $result->getProcessedCount()
                    ));
                    
                } catch (\Exception $e) {
                    $this->logger->error('Sync failed for credential', [
                        'credential_id' => $credential->getId()->toString(),
                        'company' => $credential->getCompany()->getName(),
                        'error' => $e->getMessage()
                    ]);
                    
                    $io->error(sprintf('Sync failed for %s: %s',
                        $credential->getCompany()->getName(),
                        $e->getMessage()
                    ));
                }
            }
        }
        
        $this->outputSummary($results, $io);
        
        return Command::SUCCESS;
    }
}
```

#### ServiceTitanSyncStatusCommand
```php
// bin/console servicetitan:sync-status
class ServiceTitanSyncStatusCommand extends Command
{
    protected static $defaultName = 'servicetitan:sync-status';
    protected static $defaultDescription = 'Show current ServiceTitan synchronization status';
    
    protected function configure(): void
    {
        $this
            ->addOption('company-id', 'c', InputOption::VALUE_OPTIONAL, 'Show status for specific company')
            ->addOption('running-only', 'r', InputOption::VALUE_NONE, 'Show only running syncs')
            ->addOption('failed-only', 'f', InputOption::VALUE_NONE, 'Show only failed syncs')
            ->addOption('last-24h', null, InputOption::VALUE_NONE, 'Show syncs from last 24 hours only');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $filters = SyncStatusFilters::fromInput($input);
        $syncLogs = $this->syncLogRepository->findWithFilters($filters);
        
        if (empty($syncLogs)) {
            $io->info('No sync operations found matching the criteria');
            return Command::SUCCESS;
        }
        
        $this->displaySyncTable($syncLogs, $io);
        $this->displaySummaryStats($syncLogs, $io);
        
        return Command::SUCCESS;
    }
    
    private function displaySyncTable(array $syncLogs, SymfonyStyle $io): void
    {
        $rows = [];
        
        foreach ($syncLogs as $log) {
            $rows[] = [
                $log->getId()->toString(),
                $log->getServiceTitanCredential()->getCompany()->getName(),
                $log->getDataType(),
                $log->getStatus(),
                $log->getStartedAt()->format('Y-m-d H:i:s'),
                $log->getCompletedAt()?->format('Y-m-d H:i:s') ?? 'Running',
                $log->getRecordsProcessed(),
                $log->getRecordsSuccessful(),
                $log->getRecordsFailed()
            ];
        }
        
        $io->table([
            'ID', 'Company', 'Data Type', 'Status', 'Started', 'Completed',
            'Processed', 'Successful', 'Failed'
        ], $rows);
    }
}
```

#### ServiceTitanRetryFailedSyncsCommand
```php
// bin/console servicetitan:retry-failed-syncs
class ServiceTitanRetryFailedSyncsCommand extends Command
{
    protected static $defaultName = 'servicetitan:retry-failed-syncs';
    protected static $defaultDescription = 'Retry failed ServiceTitan synchronizations';
    
    protected function configure(): void
    {
        $this
            ->addOption('max-age', 'm', InputOption::VALUE_OPTIONAL, 'Maximum age of failed syncs to retry (hours)', '24')
            ->addOption('max-retries', null, InputOption::VALUE_OPTIONAL, 'Maximum number of syncs to retry', '10')
            ->addOption('exclude-auth-errors', null, InputOption::VALUE_NONE, 'Skip syncs that failed due to authentication errors')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be retried without executing');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $maxAge = (int) $input->getOption('max-age');
        $maxRetries = (int) $input->getOption('max-retries');
        $excludeAuthErrors = $input->getOption('exclude-auth-errors');
        
        $cutoffDate = new \DateTime("-{$maxAge} hours");
        
        $failedSyncs = $this->syncLogRepository->findFailedSyncs($cutoffDate, $excludeAuthErrors);
        
        if (empty($failedSyncs)) {
            $io->info('No failed syncs found to retry');
            return Command::SUCCESS;
        }
        
        $failedSyncs = array_slice($failedSyncs, 0, $maxRetries);
        
        $io->note(sprintf('Found %d failed syncs to retry', count($failedSyncs)));
        
        if ($input->getOption('dry-run')) {
            $this->showRetryPreview($failedSyncs, $io);
            return Command::SUCCESS;
        }
        
        return $this->retryFailedSyncs($failedSyncs, $io);
    }
}
```

## Definition of Done
- [ ] Console commands implemented and registered
- [ ] All filtering options working correctly
- [ ] Error handling and logging comprehensive
- [ ] Progress reporting functional
- [ ] Dry-run mode implemented
- [ ] Unit tests for command logic passing
- [ ] Integration tests with real sync operations
- [ ] Commands properly documented
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-014**: ServiceTitan Integration Service
- **ST-002**: ServiceTitan Sync Log Entity Design

## Testing Requirements
- Unit tests for command option processing
- Unit tests for filtering logic
- Integration tests with sync service
- Test dry-run functionality
- Test error scenarios and recovery
- Test concurrent processing

### Command Test Examples
```php
class ProcessServiceTitanSyncsCommandTest extends AbstractKernelTestCase
{
    public function testProcessScheduledSyncsWithFilters(): void
    {
        $command = $this->getService(ProcessServiceTitanSyncsCommand::class);
        $commandTester = new CommandTester($command);
        
        // Create test credentials
        $credential1 = $this->createTestCredential('integration');
        $credential2 = $this->createTestCredential('production');
        
        $commandTester->execute([
            'command' => $command->getName(),
            '--environment' => 'integration',
            '--dry-run' => true
        ]);
        
        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        
        $output = $commandTester->getDisplay();
        self::assertStringContains('Found 1 credentials to process', $output);
        self::assertStringContains('integration', $output);
        self::assertStringNotContains('production', $output);
    }
    
    public function testSyncStatusCommand(): void
    {
        $command = $this->getService(ServiceTitanSyncStatusCommand::class);
        $commandTester = new CommandTester($command);
        
        // Create test sync logs
        $this->createTestSyncLogs();
        
        $commandTester->execute([
            'command' => $command->getName(),
            '--running-only' => true
        ]);
        
        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        
        $output = $commandTester->getDisplay();
        self::assertStringContains('running', $output);
        self::assertStringNotContains('completed', $output);
    }
}
```

### Concurrent Processing Test
```php
public function testConcurrentSyncProcessing(): void
{
    $command = $this->getService(ProcessServiceTitanSyncsCommand::class);
    $commandTester = new CommandTester($command);
    
    // Create multiple test credentials
    $credentials = [];
    for ($i = 0; $i < 5; $i++) {
        $credentials[] = $this->createTestCredential();
    }
    
    $startTime = microtime(true);
    
    $commandTester->execute([
        'command' => $command->getName(),
        '--max-concurrent' => '3',
        '--timeout' => '60'
    ]);
    
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    
    self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    
    // Should process faster than sequential execution
    self::assertLessThan(300, $executionTime); // Should be under 5 minutes for 5 syncs
}
```

## Cron Integration
These commands are designed for cron scheduling:

```bash
# Process scheduled syncs every hour
0 * * * * /usr/bin/php /path/to/hub-plus-api/bin/console servicetitan:process-scheduled-syncs --environment=production

# Retry failed syncs twice daily
0 6,18 * * * /usr/bin/php /path/to/hub-plus-api/bin/console servicetitan:retry-failed-syncs --max-age=12

# Daily status report
0 8 * * * /usr/bin/php /path/to/hub-plus-api/bin/console servicetitan:sync-status --last-24h
```

## Error Handling and Recovery
```php
// In command execute method
try {
    $result = $this->integrationService->synchronizeData($credential, $config);
} catch (ServiceTitanApiException $e) {
    // ServiceTitan API specific errors
    $this->logger->error('ServiceTitan API error during scheduled sync', [
        'credential_id' => $credential->getId()->toString(),
        'error' => $e->getMessage(),
        'actionable_message' => $e->getActionableMessage()
    ]);
    
    // Continue with next credential instead of failing entire batch
    continue;
} catch (InvalidCredentialsException $e) {
    // Mark credential as needing attention
    $this->credentialService->markCredentialAsError($credential, $e->getMessage());
    continue;
} catch (\Exception $e) {
    // Unexpected errors
    $this->logger->critical('Unexpected error during scheduled sync', [
        'credential_id' => $credential->getId()->toString(),
        'exception' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    throw $e; // Re-throw to fail the command
}
```

## Monitoring Integration
Commands output structured data for monitoring systems:

```php
// Output JSON summary for monitoring tools
if ($input->getOption('json-output')) {
    $summary = [
        'total_credentials' => count($credentials),
        'successful_syncs' => $successCount,
        'failed_syncs' => $failedCount,
        'total_records_processed' => $totalRecords,
        'execution_time_seconds' => $executionTime
    ];
    
    $output->write(json_encode($summary));
}
```

## Risks and Mitigation
- **Risk**: Long-running commands causing system resource issues
- **Mitigation**: Concurrent processing limits and timeout controls
- **Risk**: Credential failures affecting entire batch
- **Mitigation**: Individual error handling to continue processing other credentials

## Additional Notes
These commands form the backbone of automated ServiceTitan data synchronization and must be robust enough for production cron scheduling. They should handle failures gracefully while providing comprehensive logging for operational monitoring.
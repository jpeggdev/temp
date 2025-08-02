# User Story: ST-016 - Data Synchronization Controllers

## Story Information
- **Story ID**: ST-016
- **Epic**: User Experience & Management Interface
- **Phase**: Phase 3 - UI Components & Synchronization Services
- **Story Points**: 5
- **Priority**: Must Have
- **Component**: Controller Layer

## User Story
**As a** system administrator  
**I want** API endpoints for managing data synchronization  
**So that** I can monitor and control ServiceTitan data operations

## Detailed Description
This story creates REST API endpoints for managing ServiceTitan data synchronization operations. The controllers handle manual sync triggering, sync history retrieval, status monitoring, and dashboard data for operational visibility.

## Acceptance Criteria
- [ ] Create endpoints for manual sync triggering
- [ ] Implement sync history and status endpoints
- [ ] Add synchronization dashboard data endpoint
- [ ] Include sync schedule management endpoints
- [ ] Support filtering and pagination for history
- [ ] Add real-time sync status updates

## Technical Implementation Notes
- **Controller Location**: `src/Module/ServiceTitan/Feature/DataSynchronization/Controller/`
- **Pattern**: Follow existing API patterns
- **Architecture Reference**: Section 7.1

### Controller Endpoints

#### TriggerServiceTitanSyncController
```php
// POST /api/servicetitan/credentials/{id}/sync
class TriggerServiceTitanSyncController extends AbstractController
{
    public function __invoke(
        string $id,
        TriggerSyncRequest $request,
        ServiceTitanIntegrationService $integrationService
    ): JsonResponse {
        $credential = $integrationService->findCredential($id);
        $this->denyAccessUnlessGranted('TRIGGER_SERVICETITAN_SYNC', $credential);
        
        $config = new SyncConfiguration(
            dataType: $request->getDataType(),
            syncType: 'manual',
            dateRange: $request->getDateRange(),
            incrementalOnly: $request->isIncrementalOnly()
        );
        
        // Trigger async sync job
        $syncJob = $integrationService->scheduleSync($credential, $config);
        
        return $this->json(new SyncJobResponse($syncJob), Response::HTTP_ACCEPTED);
    }
}
```

#### GetServiceTitanSyncStatusController
```php
// GET /api/servicetitan/credentials/{id}/sync/status
class GetServiceTitanSyncStatusController extends AbstractController
{
    public function __invoke(
        string $id,
        ServiceTitanSyncLogRepository $syncLogRepository
    ): JsonResponse {
        $credential = $this->findCredentialWithAccess($id, 'VIEW_SERVICETITAN_SYNC');
        
        $currentSync = $syncLogRepository->findCurrentSync($credential);
        $lastCompletedSync = $syncLogRepository->findLastCompletedSync($credential);
        
        return $this->json(new SyncStatusResponse($currentSync, $lastCompletedSync));
    }
}
```

#### GetServiceTitanSyncHistoryController
```php
// GET /api/servicetitan/credentials/{id}/sync/history
class GetServiceTitanSyncHistoryController extends AbstractController
{
    public function __invoke(
        string $id,
        Request $request,
        ServiceTitanSyncLogRepository $syncLogRepository
    ): JsonResponse {
        $credential = $this->findCredentialWithAccess($id, 'VIEW_SERVICETITAN_SYNC');
        
        $filters = SyncHistoryFilters::fromRequest($request);
        $page = (int) $request->query->get('page', 1);
        $limit = min((int) $request->query->get('limit', 50), 100);
        
        $history = $syncLogRepository->findPaginatedHistory($credential, $filters, $page, $limit);
        
        return $this->json(new PaginatedSyncHistoryResponse($history, $page, $limit));
    }
}
```

#### GetServiceTitanDashboardDataController
```php
// GET /api/servicetitan/dashboard
class GetServiceTitanDashboardDataController extends AbstractController
{
    public function __invoke(
        ServiceTitanDashboardService $dashboardService
    ): JsonResponse {
        $company = $this->getUser()->getCurrentCompany();
        $this->denyAccessUnlessGranted('VIEW_SERVICETITAN_DASHBOARD', $company);
        
        $dashboardData = $dashboardService->getDashboardData($company);
        
        return $this->json(new ServiceTitanDashboardResponse($dashboardData));
    }
}
```

#### CancelServiceTitanSyncController
```php
// DELETE /api/servicetitan/credentials/{id}/sync/current
class CancelServiceTitanSyncController extends AbstractController
{
    public function __invoke(
        string $id,
        ServiceTitanIntegrationService $integrationService
    ): JsonResponse {
        $credential = $this->findCredentialWithAccess($id, 'CANCEL_SERVICETITAN_SYNC');
        
        $result = $integrationService->cancelCurrentSync($credential);
        
        if (!$result->wasRunning()) {
            return $this->json(['message' => 'No sync was running'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json(['message' => 'Sync cancelled successfully']);
    }
}
```

#### GetServiceTitanSyncMetricsController
```php
// GET /api/servicetitan/credentials/{id}/sync/metrics
class GetServiceTitanSyncMetricsController extends AbstractController
{
    public function __invoke(
        string $id,
        Request $request,
        ServiceTitanMetricsService $metricsService
    ): JsonResponse {
        $credential = $this->findCredentialWithAccess($id, 'VIEW_SERVICETITAN_METRICS');
        
        $timeRange = TimeRange::fromRequest($request);
        $metrics = $metricsService->getMetrics($credential, $timeRange);
        
        return $this->json(new SyncMetricsResponse($metrics));
    }
}
```

## Definition of Done
- [ ] All sync management endpoints implemented
- [ ] History and status endpoints working correctly
- [ ] Dashboard data endpoint functional
- [ ] Filtering and pagination working
- [ ] Real-time status updates implemented
- [ ] API tests for all endpoints passing
- [ ] Proper error handling for all scenarios
- [ ] Security voters integrated
- [ ] PHPStan analysis passing
- [ ] Code style compliance verified

## Dependencies
- **ST-014**: ServiceTitan Integration Service
- **ST-015**: Credential Management Controllers (shared patterns)
- **ST-017**: Request/Response DTOs
- **ST-018**: Security Voters Implementation

## Testing Requirements
- API tests for all sync management operations
- Test real-time status updates
- Test pagination and filtering
- Test dashboard data accuracy
- Test error scenarios and edge cases

### API Test Examples
```php
class ServiceTitanSyncManagementTest extends AbstractWebTestCase
{
    public function testTriggerManualSync(): void
    {
        $client = static::createClient();
        $this->loginAsCompanyAdmin($client);
        
        $credential = $this->createTestCredential();
        
        $syncRequest = [
            'dataType' => 'both',
            'dateRange' => [
                'from' => '2025-07-01',
                'to' => '2025-07-29'
            ],
            'incrementalOnly' => false
        ];
        
        $client->request(
            'POST',
            '/api/servicetitan/credentials/' . $credential->getId() . '/sync',
            [],
            [],
            [],
            json_encode($syncRequest)
        );
        
        self::assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
        
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('syncJobId', $response);
        self::assertSame('queued', $response['status']);
    }
    
    public function testGetSyncHistory(): void
    {
        $client = static::createClient();
        $this->loginAsCompanyAdmin($client);
        
        $credential = $this->createTestCredentialWithHistory();
        
        $client->request('GET', '/api/servicetitan/credentials/' . $credential->getId() . '/sync/history?page=1&limit=10');
        
        self::assertResponseIsSuccessful();
        
        $response = json_decode($client->getResponse()->getContent(), true);
        self::assertArrayHasKey('items', $response);
        self::assertArrayHasKey('pagination', $response);
        self::assertLessThanOrEqual(10, count($response['items']));
    }
}
```

### Real-time Status Test
```php
public function testRealTimeSyncStatus(): void
{
    $client = static::createClient();
    $this->loginAsCompanyAdmin($client);
    
    $credential = $this->createTestCredential();
    
    // Start sync
    $this->triggerSync($client, $credential, ['dataType' => 'customers']);
    
    // Check initial status
    $client->request('GET', '/api/servicetitan/credentials/' . $credential->getId() . '/sync/status');
    $response = json_decode($client->getResponse()->getContent(), true);
    
    self::assertSame('running', $response['currentSync']['status']);
    self::assertArrayHasKey('progress', $response['currentSync']);
}
```

## Request/Response Objects

### Request DTOs
```php
class TriggerSyncRequest
{
    #[Assert\Choice(['customers', 'invoices', 'both'])]
    public string $dataType;
    
    #[Assert\Valid]
    public ?DateRangeDto $dateRange = null;
    
    public bool $incrementalOnly = false;
}

class SyncHistoryFilters
{
    public ?string $status = null;
    public ?string $syncType = null;
    public ?string $dataType = null;
    public ?\DateTime $startDate = null;
    public ?\DateTime $endDate = null;
    
    public static function fromRequest(Request $request): self
    {
        $filters = new self();
        $filters->status = $request->query->get('status');
        $filters->syncType = $request->query->get('syncType');
        $filters->dataType = $request->query->get('dataType');
        
        if ($startDate = $request->query->get('startDate')) {
            $filters->startDate = new \DateTime($startDate);
        }
        
        return $filters;
    }
}
```

### Response DTOs
```php
class SyncStatusResponse
{
    public function __construct(
        private readonly ?ServiceTitanSyncLog $currentSync,
        private readonly ?ServiceTitanSyncLog $lastCompletedSync
    ) {}
    
    public function toArray(): array
    {
        return [
            'currentSync' => $this->currentSync ? [
                'id' => $this->currentSync->getId()->toString(),
                'status' => $this->currentSync->getStatus(),
                'dataType' => $this->currentSync->getDataType(),
                'startedAt' => $this->currentSync->getStartedAt()->format('c'),
                'progress' => [
                    'processed' => $this->currentSync->getRecordsProcessed(),
                    'successful' => $this->currentSync->getRecordsSuccessful(),
                    'failed' => $this->currentSync->getRecordsFailed()
                ]
            ] : null,
            'lastCompletedSync' => $this->lastCompletedSync ? [
                'completedAt' => $this->lastCompletedSync->getCompletedAt()->format('c'),
                'status' => $this->lastCompletedSync->getStatus(),
                'recordsProcessed' => $this->lastCompletedSync->getRecordsProcessed()
            ] : null
        ];
    }
}
```

## Real-time Updates Implementation
```php
// Using Symfony Mercure for real-time updates
class SyncProgressEventListener
{
    public function __construct(
        private readonly HubInterface $mercureHub
    ) {}
    
    public function onSyncProgress(SyncProgressEvent $event): void
    {
        $update = new Update(
            'servicetitan/sync/' . $event->getSyncLog()->getId(),
            json_encode([
                'status' => $event->getSyncLog()->getStatus(),
                'progress' => [
                    'processed' => $event->getSyncLog()->getRecordsProcessed(),
                    'successful' => $event->getSyncLog()->getRecordsSuccessful(),
                    'failed' => $event->getSyncLog()->getRecordsFailed()
                ]
            ])
        );
        
        $this->mercureHub->publish($update);
    }
}
```

## Risks and Mitigation
- **Risk**: Long-running syncs causing timeout issues
- **Mitigation**: Async job processing with status polling
- **Risk**: Overwhelming API with status requests
- **Mitigation**: Rate limiting and caching for status endpoints

## Additional Notes
These controllers provide the operational interface for ServiceTitan data synchronization. They must handle concurrent operations gracefully and provide clear status information for monitoring and troubleshooting.
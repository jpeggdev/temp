<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\DTO\ServiceTitanAlertDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanCredentialSummaryDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanDashboardDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanMetricsDTO;
use App\Module\ServiceTitan\DTO\ServiceTitanSyncSummaryDTO;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Repository\ServiceTitanCredentialRepository;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;

class ServiceTitanMetricsService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const CACHE_KEY_PREFIX = 'servicetitan_dashboard_';

    public function __construct(
        private readonly ServiceTitanCredentialRepository $credentialRepository,
        private readonly ServiceTitanSyncLogRepository $syncLogRepository,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    public function getDashboardMetrics(Company $company): ServiceTitanDashboardDTO
    {
        $cacheKey = self::CACHE_KEY_PREFIX.$company->getId();
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cachedData = $cacheItem->get();
            return new ServiceTitanDashboardDTO(
                $cachedData['credentials'],
                $cachedData['alerts'],
                $cachedData['syncSummary'],
                $cachedData['metrics'],
                $cachedData['lastUpdated']
            );
        }

        $credentials = $this->credentialRepository->findByCompany($company);
        $credentialSummaries = $this->buildCredentialSummaries($credentials);
        $alerts = $this->generateAlerts($credentials);
        $syncSummary = $this->buildSyncSummary($credentials);
        $metrics = $this->buildMetrics($credentials);
        $lastUpdated = new \DateTime();

        $dashboardData = new ServiceTitanDashboardDTO(
            $credentialSummaries,
            $alerts,
            $syncSummary,
            $metrics,
            $lastUpdated
        );

        // Cache the data
        $cacheItem->set([
            'credentials' => $credentialSummaries,
            'alerts' => $alerts,
            'syncSummary' => $syncSummary,
            'metrics' => $metrics,
            'lastUpdated' => $lastUpdated,
        ]);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);

        return $dashboardData;
    }

    /**
     * @param ServiceTitanCredential[] $credentials
     * @return ServiceTitanCredentialSummaryDTO[]
     */
    private function buildCredentialSummaries(array $credentials): array
    {
        return array_map(function (ServiceTitanCredential $credential) {
            return new ServiceTitanCredentialSummaryDTO(
                $credential->getUuid(),
                $credential->getEnvironment(),
                $credential->getConnectionStatus(),
                $credential->getLastConnectionAttempt(),
                $credential->getTokenExpiresAt(),
                $credential->hasValidCredentials(),
                $credential->hasValidTokens(),
                $credential->isActiveConnection()
            );
        }, $credentials);
    }

    /**
     * @param ServiceTitanCredential[] $credentials
     * @return ServiceTitanAlertDTO[]
     */
    private function generateAlerts(array $credentials): array
    {
        $alerts = [];

        foreach ($credentials as $credential) {
            // Check for expired tokens
            if ($credential->isTokenExpired() && $credential->hasValidCredentials()) {
                $alerts[] = new ServiceTitanAlertDTO(
                    Uuid::uuid4()->toString(),
                    ServiceTitanAlertDTO::TYPE_TOKEN,
                    ServiceTitanAlertDTO::SEVERITY_WARNING,
                    'Token Expired',
                    sprintf('Authentication token for %s environment has expired', $credential->getEnvironment()->value),
                    new \DateTime(),
                    ['environment' => $credential->getEnvironment()->value],
                    '/servicetitan/credentials/'.$credential->getUuid(),
                    'Refresh Token'
                );
            }

            // Check for inactive connections
            if ($credential->getConnectionStatus() === ServiceTitanConnectionStatus::INACTIVE && $credential->hasValidCredentials()) {
                $alerts[] = new ServiceTitanAlertDTO(
                    Uuid::uuid4()->toString(),
                    ServiceTitanAlertDTO::TYPE_CONNECTION,
                    ServiceTitanAlertDTO::SEVERITY_ERROR,
                    'Connection Inactive',
                    sprintf('Connection to %s environment is inactive', $credential->getEnvironment()->value),
                    new \DateTime(),
                    ['environment' => $credential->getEnvironment()->value],
                    '/servicetitan/credentials/'.$credential->getUuid(),
                    'Reconnect'
                );
            }

            // Check for failed syncs
            $failedSyncs = $this->syncLogRepository->findByStatus(ServiceTitanSyncStatus::FAILED, 5);
            if (count($failedSyncs) > 0) {
                $alerts[] = new ServiceTitanAlertDTO(
                    Uuid::uuid4()->toString(),
                    ServiceTitanAlertDTO::TYPE_SYNC,
                    ServiceTitanAlertDTO::SEVERITY_ERROR,
                    'Recent Sync Failures',
                    sprintf('%d recent sync operations have failed', count($failedSyncs)),
                    new \DateTime(),
                    ['failedCount' => count($failedSyncs)],
                    '/servicetitan/sync-logs',
                    'View Logs'
                );
            }
        }

        return $alerts;
    }

    /**
     * @param ServiceTitanCredential[] $credentials
     */
    private function buildSyncSummary(array $credentials): ServiceTitanSyncSummaryDTO
    {
        $thirtyDaysAgo = new \DateTime('-30 days');
        $now = new \DateTime();

        $totalSyncs = 0;
        $successfulSyncs = 0;
        $failedSyncs = 0;
        $runningSyncs = 0;
        $lastSuccessfulSync = null;
        $lastFailedSync = null;
        $allSuccessRates = [];
        $recentSyncHistory = [];

        foreach ($credentials as $credential) {
            $stats = $this->syncLogRepository->getSyncStatistics($credential, $thirtyDaysAgo);

            $totalSyncs += $stats['totalSyncs'];
            $successfulSyncs += $stats['completedSyncs'];
            $failedSyncs += $stats['failedSyncs'];
            $runningSyncs += $stats['runningSyncs'];

            if ($stats['successRate'] > 0) {
                $allSuccessRates[] = $stats['successRate'];
            }

            // Find last successful sync
            $lastSuccessful = $this->syncLogRepository->findLastSuccessfulSync($credential);
            if ($lastSuccessful && ($lastSuccessfulSync === null || $lastSuccessful->getCompletedAt() > $lastSuccessfulSync)) {
                $lastSuccessfulSync = $lastSuccessful->getCompletedAt();
            }

            // Find recent sync history
            $recentLogs = $this->syncLogRepository->findByCredential($credential, 10);
            foreach ($recentLogs as $log) {
                $recentSyncHistory[] = [
                    'id' => $log->getId(),
                    'environment' => $credential->getEnvironment()->value,
                    'dataType' => $log->getDataType()->value,
                    'status' => $log->getStatus()->value,
                    'startedAt' => $log->getStartedAt()->format(\DateTimeInterface::ATOM),
                    'completedAt' => $log->getCompletedAt()?->format(\DateTimeInterface::ATOM),
                    'recordsProcessed' => $log->getRecordsProcessed(),
                    'successRate' => $log->getSuccessRate(),
                ];
            }
        }

        // Sort recent sync history by start time desc
        usort($recentSyncHistory, fn ($a, $b) => $b['startedAt'] <=> $a['startedAt']);
        $recentSyncHistory = array_slice($recentSyncHistory, 0, 20); // Keep only latest 20

        $averageSuccessRate = count($allSuccessRates) > 0 ? array_sum($allSuccessRates) / count($allSuccessRates) : 0.0;

        return new ServiceTitanSyncSummaryDTO(
            $totalSyncs,
            $successfulSyncs,
            $failedSyncs,
            $runningSyncs,
            $lastSuccessfulSync,
            $lastFailedSync,
            $averageSuccessRate,
            $recentSyncHistory
        );
    }

    /**
     * @param ServiceTitanCredential[] $credentials
     */
    private function buildMetrics(array $credentials): ServiceTitanMetricsDTO
    {
        $thirtyDaysAgo = new \DateTime('-30 days');
        $now = new \DateTime();

        $totalMembers = 0;
        $totalInvoices = 0;
        $membersLastMonth = 0;
        $invoicesLastMonth = 0;
        $dataTypeMetrics = [];
        $environmentMetrics = [];

        foreach ($credentials as $credential) {
            $stats = $this->syncLogRepository->getSyncStatistics($credential, $thirtyDaysAgo);

            // For now, we'll simulate member/invoice metrics based on processed records
            // In a real implementation, you'd query actual member/invoice data
            $totalMembers += (int) ($stats['totalRecordsSuccessful'] * 0.6); // Assume 60% are members
            $totalInvoices += (int) ($stats['totalRecordsSuccessful'] * 0.4); // Assume 40% are invoices

            $membersLastMonth += (int) ($stats['totalRecordsSuccessful'] * 0.6);
            $invoicesLastMonth += (int) ($stats['totalRecordsSuccessful'] * 0.4);

            // Environment metrics
            $envKey = $credential->getEnvironment()->value;
            if (!isset($environmentMetrics[$envKey])) {
                $environmentMetrics[$envKey] = [
                    'environment' => $envKey,
                    'syncCount' => 0,
                    'successRate' => 0.0,
                    'recordsProcessed' => 0,
                    'isActive' => false,
                ];
            }

            $environmentMetrics[$envKey]['syncCount'] += $stats['totalSyncs'];
            $environmentMetrics[$envKey]['successRate'] = $stats['successRate'];
            $environmentMetrics[$envKey]['recordsProcessed'] += $stats['totalRecordsProcessed'];
            $environmentMetrics[$envKey]['isActive'] = $credential->isActiveConnection();
        }

        // Data type metrics (simulated)
        $dataTypeMetrics = [
            [
                'dataType' => ServiceTitanSyncDataType::CUSTOMERS->value,
                'totalRecords' => $totalMembers,
                'lastSync' => $now->format(\DateTimeInterface::ATOM),
                'successRate' => 95.2,
            ],
            [
                'dataType' => ServiceTitanSyncDataType::INVOICES->value,
                'totalRecords' => $totalInvoices,
                'lastSync' => $now->format(\DateTimeInterface::ATOM),
                'successRate' => 98.1,
            ],
        ];

        // Calculate growth rates (simplified - would need historical data in real implementation)
        $memberGrowthRate = $membersLastMonth > 0 ? 5.2 : 0.0; // Simulated 5.2% growth
        $invoiceGrowthRate = $invoicesLastMonth > 0 ? 8.7 : 0.0; // Simulated 8.7% growth

        return new ServiceTitanMetricsDTO(
            $totalMembers,
            $totalInvoices,
            $membersLastMonth,
            $invoicesLastMonth,
            $memberGrowthRate,
            $invoiceGrowthRate,
            $dataTypeMetrics,
            array_values($environmentMetrics)
        );
    }

    public function clearDashboardCache(Company $company): void
    {
        $cacheKey = self::CACHE_KEY_PREFIX.$company->getId();
        $this->cache->deleteItem($cacheKey);
    }
}

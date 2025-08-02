<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\DataSynchronization\Service;

use App\Entity\Company;
use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use Psr\Log\LoggerInterface;

readonly class ServiceTitanAlertService
{
    public function __construct(
        private ServiceTitanSyncLogRepository $syncLogRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Check for sync failures and generate alerts
     */
    public function checkForSyncFailures(): array
    {
        $alerts = [];

        // Check for failed syncs in the last 24 hours
        $yesterday = new \DateTime('-24 hours');
        $failedSyncs = $this->syncLogRepository->findRecentLogs($yesterday, 100);

        foreach ($failedSyncs as $syncLog) {
            if ($syncLog->hasFailed()) {
                $alerts[] = $this->createFailureAlert($syncLog);
            }
        }

        // Check for long-running syncs (over 1 hour)
        $oneHourAgo = new \DateTime('-1 hour');
        $longRunningSyncs = $this->syncLogRepository->findByStatus(ServiceTitanSyncStatus::RUNNING, 50);

        foreach ($longRunningSyncs as $syncLog) {
            if ($syncLog->getStartedAt() < $oneHourAgo) {
                $alerts[] = $this->createLongRunningSyncAlert($syncLog);
            }
        }

        if (!empty($alerts)) {
            $this->logger->warning('ServiceTitan sync alerts generated', [
                'alertCount' => count($alerts),
                'alerts' => array_map(fn ($alert) => $alert['type'].': '.$alert['message'], $alerts)
            ]);
        }

        return $alerts;
    }

    /**
     * Get sync health metrics for a company
     */
    public function getSyncHealthMetrics(Company $company): array
    {
        $startDate = new \DateTime('-7 days');
        $endDate = new \DateTime();

        // Get all credentials for the company
        $companyCredentials = []; // TODO: Add method to get credentials by company

        $overallMetrics = [
            'totalSyncs' => 0,
            'successfulSyncs' => 0,
            'failedSyncs' => 0,
            'avgProcessingTime' => 0.0,
            'successRate' => 0.0,
            'alertLevel' => 'healthy',
            'lastSyncAt' => null,
        ];

        // For now, return basic structure
        // TODO: Implement when company->credential relationship is established

        return $overallMetrics;
    }

    /**
     * Get system-wide sync health dashboard
     */
    public function getSystemHealthDashboard(): array
    {
        $now = new \DateTime();
        $last24Hours = new \DateTime('-24 hours');
        $lastWeek = new \DateTime('-7 days');

        // Get recent sync statistics
        $recentSyncs = $this->syncLogRepository->findRecentLogs($last24Hours, 1000);

        $metrics = [
            'last24Hours' => $this->calculatePeriodMetrics($recentSyncs, $last24Hours),
            'activeSyncs' => $this->getActiveSyncCount(),
            'systemStatus' => $this->determineSystemStatus($recentSyncs),
            'alerts' => $this->checkForSyncFailures(),
            'updatedAt' => $now->format('c'),
        ];

        return $metrics;
    }

    /**
     * Check if a sync should trigger an alert
     */
    public function shouldAlertForSync(ServiceTitanSyncLog $syncLog): bool
    {
        // Alert conditions:
        // 1. Sync failed
        if ($syncLog->hasFailed()) {
            return true;
        }

        // 2. Sync running for over 2 hours
        if ($syncLog->isRunning()) {
            $twoHoursAgo = new \DateTime('-2 hours');
            if ($syncLog->getStartedAt() < $twoHoursAgo) {
                return true;
            }
        }

        // 3. Success rate below 80%
        return $syncLog->getRecordsProcessed() > 10 && $syncLog->getSuccessRate() < 80;
    }

    /**
     * Create a failure alert
     */
    private function createFailureAlert(ServiceTitanSyncLog $syncLog): array
    {
        $serviceTitanCredential = $syncLog->getServiceTitanCredential();
        return [
            'type' => 'sync_failure',
            'severity' => 'high',
            'syncLogId' => $syncLog->getId(),
            'credentialId' => $serviceTitanCredential->getId(),
            'companyName' => $serviceTitanCredential->getCompany()?->getCompanyName(),
            'message' => sprintf(
                'ServiceTitan sync failed for %s (%s data)',
                $serviceTitanCredential->getCompany()?->getCompanyName(),
                $syncLog->getDataType()->value
            ),
            'details' => [
                'dataType' => $syncLog->getDataType()->value,
                'startedAt' => $syncLog->getStartedAt()->format('c'),
                'failedAt' => $syncLog->getCompletedAt()?->format('c'),
                'errorMessage' => $syncLog->getErrorMessage(),
                'recordsProcessed' => $syncLog->getRecordsProcessed(),
                'recordsFailed' => $syncLog->getRecordsFailed(),
            ],
            'createdAt' => (new \DateTime())->format('c'),
        ];
    }

    /**
     * Create a long-running sync alert
     */
    private function createLongRunningSyncAlert(ServiceTitanSyncLog $syncLog): array
    {
        $elapsedTime = (new \DateTime())->getTimestamp() - $syncLog->getStartedAt()->getTimestamp();

        return [
            'type' => 'long_running_sync',
            'severity' => 'medium',
            'syncLogId' => $syncLog->getId(),
            'credentialId' => $syncLog->getServiceTitanCredential()->getId(),
            'companyName' => $syncLog->getServiceTitanCredential()->getCompany()?->getCompanyName(),
            'message' => sprintf(
                'ServiceTitan sync running for %d minutes for %s',
                floor($elapsedTime / 60),
                $syncLog->getServiceTitanCredential()->getCompany()?->getCompanyName()
            ),
            'details' => [
                'dataType' => $syncLog->getDataType()->value,
                'startedAt' => $syncLog->getStartedAt()->format('c'),
                'elapsedMinutes' => floor($elapsedTime / 60),
                'recordsProcessed' => $syncLog->getRecordsProcessed(),
                'currentSuccessRate' => $syncLog->getSuccessRate(),
            ],
            'createdAt' => (new \DateTime())->format('c'),
        ];
    }

    /**
     * Calculate metrics for a specific time period
     */
    private function calculatePeriodMetrics(array $syncs, \DateTime $since): array
    {
        $total = 0;
        $successful = 0;
        $failed = 0;
        $totalProcessingTime = 0;
        $completedSyncs = 0;

        foreach ($syncs as $syncLog) {
            if ($syncLog->getStartedAt() >= $since) {
                $total++;

                if ($syncLog->wasSuccessful()) {
                    $successful++;
                }

                if ($syncLog->hasFailed()) {
                    $failed++;
                }

                if ($syncLog->getProcessingTimeSeconds() !== null) {
                    $totalProcessingTime += $syncLog->getProcessingTimeSeconds();
                    $completedSyncs++;
                }
            }
        }

        return [
            'totalSyncs' => $total,
            'successfulSyncs' => $successful,
            'failedSyncs' => $failed,
            'successRate' => $total > 0 ? ($successful / $total) * 100 : 0,
            'avgProcessingTime' => $completedSyncs > 0 ? $totalProcessingTime / $completedSyncs : 0,
        ];
    }

    /**
     * Get count of currently active syncs
     */
    private function getActiveSyncCount(): int
    {
        $activeSyncs = $this->syncLogRepository->findByStatus(ServiceTitanSyncStatus::RUNNING, 1000);
        return count($activeSyncs);
    }

    /**
     * Determine overall system status
     */
    private function determineSystemStatus(array $recentSyncs): string
    {
        if (empty($recentSyncs)) {
            return 'unknown';
        }

        $totalSyncs = count($recentSyncs);
        $failedSyncs = array_filter($recentSyncs, fn ($sync) => $sync->hasFailed());
        $failureRate = count($failedSyncs) / $totalSyncs;

        if ($failureRate > 0.2) { // More than 20% failure rate
            return 'critical';
        }

        if ($failureRate > 0.1) { // More than 10% failure rate
            return 'warning';
        }

        return 'healthy';
    }
}

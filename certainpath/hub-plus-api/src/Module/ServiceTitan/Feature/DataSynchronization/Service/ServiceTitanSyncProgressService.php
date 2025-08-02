<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\DataSynchronization\Service;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use Psr\Log\LoggerInterface;

readonly class ServiceTitanSyncProgressService
{
    public function __construct(
        private ServiceTitanSyncLogRepository $syncLogRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Get real-time progress for active syncs
     */
    public function getActiveProgressForCredential(ServiceTitanCredential $credential): array
    {
        $runningSync = $this->syncLogRepository->findRunningByCredentialSingle($credential);

        if ($runningSync === null) {
            return [
                'hasActiveSync' => false,
                'activeSyncs' => [],
            ];
        }

        $activeSyncs = [];
        $activeSyncs[] = $this->formatSyncProgress($runningSync);

        return [
            'hasActiveSync' => true,
            'activeSyncs' => $activeSyncs,
        ];
    }

    /**
     * Update sync progress (called during sync processing)
     */
    public function updateSyncProgress(
        ServiceTitanSyncLog $syncLog,
        int $recordsProcessed,
        int $recordsSuccessful,
        int $recordsFailed,
        ?string $currentOperation = null
    ): void {
        $syncLog->updateRecordCounts($recordsProcessed, $recordsSuccessful, $recordsFailed);
        $this->syncLogRepository->save($syncLog, true);

        $this->logger->info('ServiceTitan sync progress updated', [
            'syncLogId' => $syncLog->getId(),
            'credentialId' => $syncLog->getServiceTitanCredential()->getId(),
            'totalProcessed' => $syncLog->getRecordsProcessed(),
            'totalSuccessful' => $syncLog->getRecordsSuccessful(),
            'totalFailed' => $syncLog->getRecordsFailed(),
            'currentOperation' => $currentOperation,
        ]);

        // TODO: When Mercure is configured, publish real-time updates here
    }

    /**
     * Get progress statistics for all active syncs (dashboard view)
     */
    public function getGlobalSyncProgress(): array
    {
        $allRunningSyncs = $this->syncLogRepository->findByStatus(
            \App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus::RUNNING,
            100
        );

        if (empty($allRunningSyncs)) {
            return [
                'hasActiveSyncs' => false,
                'totalActiveSyncs' => 0,
                'syncs' => [],
            ];
        }

        $syncs = [];
        foreach ($allRunningSyncs as $syncLog) {
            $syncs[] = $this->formatSyncProgress($syncLog);
        }

        return [
            'hasActiveSyncs' => true,
            'totalActiveSyncs' => count($allRunningSyncs),
            'syncs' => $syncs,
        ];
    }

    /**
     * Format sync log for progress display
     */
    private function formatSyncProgress(ServiceTitanSyncLog $syncLog): array
    {
        $startTime = $syncLog->getStartedAt();
        $currentTime = new \DateTime();
        $elapsedSeconds = $currentTime->getTimestamp() - $startTime->getTimestamp();

        // Calculate estimated completion based on current progress
        $estimatedCompletion = null;
        if ($syncLog->getRecordsProcessed() > 0) {
            // This is a rough estimate - in a real implementation, you'd have better metrics
            $processingRate = $syncLog->getRecordsProcessed() / max(1, $elapsedSeconds);
            if ($processingRate > 0) {
                // Assume we need to process at least as many records as we've already processed
                $estimatedRemainingRecords = max(0, $syncLog->getRecordsProcessed() * 0.1); // 10% buffer
                $estimatedRemainingTime = $estimatedRemainingRecords / $processingRate;
                $estimatedCompletion = (new \DateTime())
                    ->add(new \DateInterval('PT'.(int) $estimatedRemainingTime.'S'))
                    ->format('c');
            }
        }

        return [
            'syncLogId' => $syncLog->getId(),
            'credentialId' => $syncLog->getServiceTitanCredential()->getId(),
            'companyName' => $syncLog->getServiceTitanCredential()->getCompany()?->getCompanyName(),
            'dataType' => $syncLog->getDataType()->value,
            'syncType' => $syncLog->getSyncType()?->value,
            'startedAt' => $startTime->format('c'),
            'elapsedTime' => $this->formatElapsedTime($elapsedSeconds),
            'progress' => [
                'recordsProcessed' => $syncLog->getRecordsProcessed(),
                'recordsSuccessful' => $syncLog->getRecordsSuccessful(),
                'recordsFailed' => $syncLog->getRecordsFailed(),
                'successRate' => $syncLog->getSuccessRate(),
            ],
            'estimatedCompletion' => $estimatedCompletion,
            'status' => $syncLog->getStatus()->value,
        ];
    }

    /**
     * Format elapsed time for human readability
     */
    private function formatElapsedTime(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $minutes.'m '.$remainingSeconds.'s';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours.'h '.$remainingMinutes.'m';
    }

}

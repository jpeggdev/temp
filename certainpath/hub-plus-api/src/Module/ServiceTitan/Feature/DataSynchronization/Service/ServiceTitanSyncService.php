<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Feature\DataSynchronization\Service;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanConnectionStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use App\Module\ServiceTitan\Repository\ServiceTitanSyncLogRepository;
use Psr\Log\LoggerInterface;

readonly class ServiceTitanSyncService
{
    public function __construct(
        private ServiceTitanSyncLogRepository $syncLogRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Trigger a new sync operation
     */
    public function triggerSync(
        ServiceTitanCredential $credential,
        ServiceTitanSyncDataType $dataType,
        ServiceTitanSyncType $syncType
    ): ServiceTitanSyncLog {
        // Validate credential is active
        if ($credential->getConnectionStatus() !== ServiceTitanConnectionStatus::ACTIVE) {
            throw new \InvalidArgumentException('ServiceTitan credential is not active');
        }

        // Check if there's already a running sync
        if ($this->syncLogRepository->hasActiveSyncForCredential($credential)) {
            throw new \RuntimeException('A sync is already running for this credential');
        }

        // Create sync log entry
        $syncLog = new ServiceTitanSyncLog();
        $syncLog->setServiceTitanCredential($credential)
            ->setSyncType($syncType)
            ->setDataType($dataType);

        $this->syncLogRepository->save($syncLog, true);

        $this->logger->info('ServiceTitan sync triggered', [
            'credentialId' => $credential->getId(),
            'companyId' => $credential->getCompany()?->getId(),
            'dataType' => $dataType->value,
            'syncType' => $syncType->value,
            'syncLogId' => $syncLog->getId(),
        ]);

        // TODO: Queue actual sync job with Symfony Messenger
        // For now, we just create the sync log entry

        return $syncLog;
    }

    /**
     * Get current sync status for a credential
     */
    public function getSyncStatus(ServiceTitanCredential $credential): array
    {
        $currentSync = $this->findCurrentRunningSyncLog($credential);
        $lastCompletedSync = $this->findLastCompletedSyncLog($credential);

        return [
            'currentSync' => $currentSync ? $this->formatSyncLogForStatus($currentSync) : null,
            'lastCompletedSync' => $lastCompletedSync ? $this->formatSyncLogForStatus($lastCompletedSync) : null,
        ];
    }

    /**
     * Get sync history for a credential with pagination
     */
    public function getSyncHistory(
        ServiceTitanCredential $credential,
        int $page = 1,
        int $limit = 50
    ): array {
        $history = $this->syncLogRepository->findByCredential($credential, $limit);

        return [
            'items' => array_map([$this, 'formatSyncLogForHistory'], $history),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => count($history), // Simplified for now
            ],
        ];
    }

    /**
     * Cancel a running sync
     */
    public function cancelSync(ServiceTitanCredential $credential): bool
    {
        $runningSync = $this->syncLogRepository->findRunningByCredentialSingle($credential);

        if ($runningSync === null) {
            return false;
        }

        $runningSync->markAsFailed('Sync cancelled by user');
        $this->syncLogRepository->save($runningSync, true);

        $this->logger->info('ServiceTitan sync cancelled', [
            'credentialId' => $credential->getId(),
            'syncLogId' => $runningSync->getId(),
        ]);

        return true;
    }

    private function findCurrentRunningSyncLog(ServiceTitanCredential $credential): ?ServiceTitanSyncLog
    {
        return $this->syncLogRepository->findRunningByCredentialSingle($credential);
    }

    private function findLastCompletedSyncLog(ServiceTitanCredential $credential): ?ServiceTitanSyncLog
    {
        // Try to find the most recent completed sync of any data type
        $completedSyncs = $this->syncLogRepository->findByCredential($credential, 1);

        foreach ($completedSyncs as $syncLog) {
            if ($syncLog->isCompleted()) {
                return $syncLog;
            }
        }

        return null;
    }

    private function formatSyncLogForStatus(ServiceTitanSyncLog $syncLog): array
    {
        return [
            'id' => $syncLog->getId(),
            'status' => $syncLog->getStatus()->value,
            'dataType' => $syncLog->getDataType()->value,
            'syncType' => $syncLog->getSyncType()?->value,
            'startedAt' => $syncLog->getStartedAt()->format('c'),
            'completedAt' => $syncLog->getCompletedAt()?->format('c'),
            'progress' => [
                'processed' => $syncLog->getRecordsProcessed(),
                'successful' => $syncLog->getRecordsSuccessful(),
                'failed' => $syncLog->getRecordsFailed(),
            ],
            'duration' => $syncLog->getDurationString(),
            'successRate' => $syncLog->getSuccessRate(),
        ];
    }

    private function formatSyncLogForHistory(ServiceTitanSyncLog $syncLog): array
    {
        return $this->formatSyncLogForStatus($syncLog);
    }
}

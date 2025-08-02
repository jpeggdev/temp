<?php

declare(strict_types=1);

namespace App\Module\ServiceTitan\Repository;

use App\Module\ServiceTitan\Entity\ServiceTitanCredential;
use App\Module\ServiceTitan\Entity\ServiceTitanSyncLog;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncDataType;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncStatus;
use App\Module\ServiceTitan\Enum\ServiceTitanSyncType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceTitanSyncLog>
 */
class ServiceTitanSyncLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceTitanSyncLog::class);
    }

    public function save(ServiceTitanSyncLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceTitanSyncLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRunningSync(ServiceTitanCredential $credential): ?ServiceTitanSyncLog
    {
        return $this->findOneBy([
            'serviceTitanCredential' => $credential,
            'status' => ServiceTitanSyncStatus::RUNNING
        ]);
    }

    public function findRecentSyncs(ServiceTitanCredential $credential, int $limit = 10): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.serviceTitanCredential = :credential')
            ->setParameter('credential', $credential)
            ->orderBy('s.startedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecentFailedSyncs(\DateTime $since): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->andWhere('s.completedAt >= :since')
            ->setParameter('status', ServiceTitanSyncStatus::FAILED)
            ->setParameter('since', $since)
            ->orderBy('s.completedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findLongRunningSyncs(int $minutesThreshold = 60): array
    {
        $threshold = new \DateTime();
        $threshold->modify("-{$minutesThreshold} minutes");

        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->andWhere('s.startedAt <= :threshold')
            ->setParameter('status', ServiceTitanSyncStatus::RUNNING)
            ->setParameter('threshold', $threshold)
            ->orderBy('s.startedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentLogs(\DateTime $since, int $limit = 100): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.startedAt >= :since')
            ->setParameter('since', $since)
            ->orderBy('s.startedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByStatus(ServiceTitanSyncStatus $status, int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', $status)
            ->orderBy('s.startedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRunningByCredential(ServiceTitanCredential $credential): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.serviceTitanCredential = :credential')
            ->andWhere('s.status = :status')
            ->setParameter('credential', $credential)
            ->setParameter('status', ServiceTitanSyncStatus::RUNNING)
            ->orderBy('s.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRunningByCredentialSingle(ServiceTitanCredential $credential): ?ServiceTitanSyncLog
    {
        return $this->createQueryBuilder('s')
            ->where('s.serviceTitanCredential = :credential')
            ->andWhere('s.status = :status')
            ->setParameter('credential', $credential)
            ->setParameter('status', ServiceTitanSyncStatus::RUNNING)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasActiveSyncForCredential(ServiceTitanCredential $credential): bool
    {
        $count = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.serviceTitanCredential = :credential')
            ->andWhere('s.status IN (:statuses)')
            ->setParameter('credential', $credential)
            ->setParameter('statuses', [ServiceTitanSyncStatus::RUNNING, ServiceTitanSyncStatus::PENDING])
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function findByCredential(ServiceTitanCredential $credential, int $limit = 50): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.serviceTitanCredential = :credential')
            ->setParameter('credential', $credential)
            ->orderBy('s.startedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getSyncStatistics(ServiceTitanCredential $credential, \DateTime $since, ?\DateTime $until = null): array
    {
        // Build base query conditions
        $baseConditions = [
            's.serviceTitanCredential = :credential',
            's.startedAt >= :since'
        ];
        $parameters = [
            'credential' => $credential,
            'since' => $since
        ];

        if ($until !== null) {
            $baseConditions[] = 's.startedAt <= :until';
            $parameters['until'] = $until;
        }

        // Total syncs
        $totalQb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)');
        foreach ($baseConditions as $condition) {
            $totalQb->andWhere($condition);
        }
        foreach ($parameters as $key => $value) {
            $totalQb->setParameter($key, $value);
        }
        $totalSyncs = (int) $totalQb->getQuery()->getSingleScalarResult();

        // Completed syncs
        $completedQb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.status = :status');
        foreach ($baseConditions as $condition) {
            $completedQb->andWhere($condition);
        }
        foreach ($parameters as $key => $value) {
            $completedQb->setParameter($key, $value);
        }
        $completedQb->setParameter('status', ServiceTitanSyncStatus::COMPLETED);
        $completedSyncs = (int) $completedQb->getQuery()->getSingleScalarResult();

        // Failed syncs
        $failedQb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.status = :status');
        foreach ($baseConditions as $condition) {
            $failedQb->andWhere($condition);
        }
        foreach ($parameters as $key => $value) {
            $failedQb->setParameter($key, $value);
        }
        $failedQb->setParameter('status', ServiceTitanSyncStatus::FAILED);
        $failedSyncs = (int) $failedQb->getQuery()->getSingleScalarResult();

        // Running syncs
        $runningQb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.status = :status');
        foreach ($baseConditions as $condition) {
            $runningQb->andWhere($condition);
        }
        foreach ($parameters as $key => $value) {
            $runningQb->setParameter($key, $value);
        }
        $runningQb->setParameter('status', ServiceTitanSyncStatus::RUNNING);
        $runningSyncs = (int) $runningQb->getQuery()->getSingleScalarResult();

        // Average processing time (only for completed syncs)
        $avgQb = $this->createQueryBuilder('s')
            ->select('AVG(s.processingTimeSeconds)')
            ->andWhere('s.status = :status')
            ->andWhere('s.processingTimeSeconds IS NOT NULL');
        foreach ($baseConditions as $condition) {
            $avgQb->andWhere($condition);
        }
        foreach ($parameters as $key => $value) {
            $avgQb->setParameter($key, $value);
        }
        $avgQb->setParameter('status', ServiceTitanSyncStatus::COMPLETED);
        $avgProcessingTime = $avgQb->getQuery()->getSingleScalarResult();

        // Total records aggregated
        $recordsQb = $this->createQueryBuilder('s')
            ->select([
                'SUM(s.recordsProcessed) as totalRecordsProcessed',
                'SUM(s.recordsSuccessful) as totalRecordsSuccessful',
                'SUM(s.recordsFailed) as totalRecordsFailed'
            ]);
        foreach ($baseConditions as $condition) {
            $recordsQb->andWhere($condition);
        }
        foreach ($parameters as $key => $value) {
            $recordsQb->setParameter($key, $value);
        }
        $recordsResult = $recordsQb->getQuery()->getSingleResult();

        $totalRecordsProcessed = (int) ($recordsResult['totalRecordsProcessed'] ?? 0);
        $totalRecordsSuccessful = (int) ($recordsResult['totalRecordsSuccessful'] ?? 0);

        return [
            'totalSyncs' => $totalSyncs,
            'completedSyncs' => $completedSyncs,
            'successfulSyncs' => $completedSyncs, // Alias for compatibility
            'failedSyncs' => $failedSyncs,
            'runningSyncs' => $runningSyncs,
            'totalRecordsProcessed' => $totalRecordsProcessed,
            'totalRecordsSuccessful' => $totalRecordsSuccessful,
            'totalRecordsFailed' => (int) ($recordsResult['totalRecordsFailed'] ?? 0),
            'avgProcessingTime' => $avgProcessingTime ? (float) $avgProcessingTime : 0.0,
            'successRate' => $totalRecordsProcessed > 0 ? ($totalRecordsSuccessful / $totalRecordsProcessed) * 100 : 0.0
        ];
    }

    public function findLastSuccessfulSync(ServiceTitanCredential $credential, ?ServiceTitanSyncDataType $dataType = null): ?ServiceTitanSyncLog
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.serviceTitanCredential = :credential')
            ->andWhere('s.status = :status')
            ->setParameter('credential', $credential)
            ->setParameter('status', ServiceTitanSyncStatus::COMPLETED);

        if ($dataType !== null) {
            $qb->andWhere('s.dataType = :dataType')
               ->setParameter('dataType', $dataType);
        }

        return $qb->orderBy('s.completedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findBySyncAndDataType(ServiceTitanSyncType $syncType, ServiceTitanSyncDataType $dataType): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.syncType = :syncType')
            ->andWhere('s.dataType = :dataType')
            ->setParameter('syncType', $syncType)
            ->setParameter('dataType', $dataType)
            ->orderBy('s.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findFailedLogs(?\DateTime $since = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.status = :status')
            ->setParameter('status', ServiceTitanSyncStatus::FAILED);

        if ($since !== null) {
            $qb->andWhere('s.completedAt >= :since')
               ->setParameter('since', $since);
        }

        return $qb->orderBy('s.completedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function deleteLogsOlderThan(\DateTime $cutoffDate): int
    {
        return $this->createQueryBuilder('s')
            ->delete()
            ->where('s.startedAt < :cutoff')
            ->setParameter('cutoff', $cutoffDate)
            ->getQuery()
            ->execute();
    }
}

<?php

namespace App\Repository\Unmanaged;

use Doctrine\DBAL\ArrayParameterType;

class HubPlusRepository extends AbstractUnmanagedRepository
{
    public const DATABASE_URL = 'DATABASE_URL_HUB_PLUS';
    public const DATABASE_TABLES = [
        'company_data_import_job'
    ];

    protected string $databaseUrl = self::DATABASE_URL;
    protected array $databaseTables = self::DATABASE_TABLES;

    private function markImportAsCompleted(int $importId): void
    {
        $qbUpdate = $this->db->createQueryBuilder()
            ->update('company_data_import_job')
            ->set('status', ':completedStatus')
            ->set('progress_percent', ':progressPercent')
            ->set('progress', ':progress')
            ->set('updated_at', 'CURRENT_TIMESTAMP')
            ->where('id = :id')
            ->setParameter('completedStatus', 'COMPLETED')
            ->setParameter('progressPercent', 100)
            ->setParameter('progress', 'Import completed')
            ->setParameter('id', $importId);

        $qbUpdate->executeQuery();
    }

    public function getInProgressImportJobsForTenant(string $tenant, ?string $table = null): array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('company_data_import_job')
            ->where('intacct_id = :tenant')
            ->andWhere('status = :status')
            ->andWhere('ready_for_unification_processing = TRUE')
            ->setParameter('tenant', $tenant)
            ->setParameter('status', 'PROCESSING');

        if ($table === 'prospects_stream') {
            $qb->andWhere('is_prospects_file = TRUE');
        } elseif ($table === 'members_stream') {
            $qb->andWhere('is_member_file = TRUE');
        } elseif ($table === 'invoices_stream') {
            $qb->andWhere('is_jobs_or_invoice_file = TRUE');
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getJob(int $importId): ?array
    {
        $qb = $this->db->createQueryBuilder()
            ->select('*')
            ->from('company_data_import_job')
            ->where('id = :id')
            ->setParameter('id', $importId)
            ->setMaxResults(1);

        $row = $qb->executeQuery()->fetchAssociative();
        return $row ?: null;
    }

    public function setImportProgress(int $importId, int $remainingCount): void
    {
        $job = $this->getJob($importId);
        if (!$job) {
            return;
        }

        $rowCount = (int) ($job['row_count'] ?? 0);

        if ($rowCount === 0 || $remainingCount === 0) {
            $this->markImportAsCompleted($importId);
            return;
        }

        $processed = $rowCount - $remainingCount;
        if ($processed < 0) {
            $processed = 0;
        }

        $fraction = $processed / $rowCount;

        $progressPercent = 50 + ($fraction * 50);
        if ($progressPercent > 100) {
            $progressPercent = 100;
        }

        $progressText = sprintf(
            'Imported/Excluded %d of %d rows',
            $processed,
            $rowCount
        );

        $qbUpdate = $this->db->createQueryBuilder()
            ->update('company_data_import_job')
            ->set('progress', ':progress')
            ->set('progress_percent', ':progressPercent')
            ->set('updated_at', 'CURRENT_TIMESTAMP')
            ->where('id = :id')
            ->setParameter('progress', $progressText)
            ->setParameter('progressPercent', $progressPercent)
            ->setParameter('id', $importId);

        $qbUpdate->executeQuery();
    }

}
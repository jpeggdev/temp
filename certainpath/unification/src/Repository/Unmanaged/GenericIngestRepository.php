<?php

namespace App\Repository\Unmanaged;

use App\Entity\Company;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Connection;

class GenericIngestRepository extends AbstractUnmanagedRepository
{
    public const DATABASE_URL = 'DATABASE_URL_GENERIC_INGEST';
    public const DATABASE_TABLES = [
        'invoices_stream',
        'members_stream',
        'prospects_stream',
    ];

    protected string $databaseUrl = self::DATABASE_URL;
    protected array $databaseTables = self::DATABASE_TABLES;

    public function countTable(string $table): int
    {
        return $this->count($table, [ ]);
    }

    public function count(string $table, array $params = [ ]): int
    {
        $this->validateTable($table);

        $query = $this->db->createQueryBuilder()
            ->select('COUNT(*)')
            ->from($table)
            ->setParameters($params)
        ;

        foreach ($params as $key => $param) {
            $query->andWhere(sprintf(
                "%s LIKE :%s",
                $key,
                $key
            ));
        }

        return (int) $query->executeQuery()
            ->fetchOne();
    }

    /**
     * @throws Exception
     */
    public function deleteById(string $table, string|array $ids): void
    {
        $this->validateTable($table);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $chunks = array_chunk($ids, 1000);

        foreach ($chunks as $chunk) {
            $qb = $this->db->createQueryBuilder()
                ->delete($table)
                ->where('id IN(:ids)')
                ->setParameter('ids', $chunk, ArrayParameterType::INTEGER);

            $qb->executeQuery();
        }
    }

    public function deleteByTenant(string $table, string $tenant): void
    {
        $this->validateTable($table);

        $qb = $this->db->createQueryBuilder()
            ->delete($table)
            ->where('tenant = :tenant')
            ->setParameters([
                'tenant' => $tenant
            ])
        ;

        $qb->executeQuery();
    }

    public function fetchLast(string $table, array $params = [ ]): array
    {
        $this->validateTable($table);

        $query = $this->db->createQueryBuilder()
            ->select('*')
            ->from($table)
            ->setParameters($params)
            ->orderBy('id', 'DESC')
            ->setMaxResults(1)
        ;

        foreach ($params as $key => $param) {
            $query->andWhere(sprintf(
                "%s LIKE :%s",
                $key,
                $key
            ));
        }

        try {
            $result = $query->executeQuery()->fetchAssociative() ?: [ ];
        } catch (\Exception) {
            $result = [ ];
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function getLatestMemberVersionForCompany(Company $company): ?string
    {
        $query = $this->db->createQueryBuilder()
            ->select('MAX(version)')
            ->from('members_stream')
            ->where('tenant = :tenant')
            ->setParameter('tenant', $company->getIdentifier())
        ;

        return $query->executeQuery()->fetchOne();
    }

    public function getRowCountForHubPlusImportId(
        string $table,
        string $tenant,
        int $importId
    ): int {
        $this->validateTable($table);

        $qb = $this->db->createQueryBuilder()
            ->select('COUNT(*) AS row_count')
            ->from($table)
            ->where('tenant = :tenant')
            ->andWhere('hub_plus_import_id = :importId')
            ->setParameter('tenant', $tenant)
            ->setParameter('importId', $importId);

        $rowCount = $qb->executeQuery()->fetchOne();
        return (int) ($rowCount ?? 0);
    }

}
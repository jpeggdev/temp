<?php

namespace App\Repository\Unmanaged;
use Doctrine\DBAL\ArrayParameterType;

class FieldEdgeRepository extends AbstractUnmanagedRepository
{
    public const DATABASE_URL = 'DATABASE_URL_FIELD_EDGE_INGEST';
    public const DATABASE_TABLES = [
        'invoices_stream',
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

        foreach($params as $key => $param) {
            $query->andWhere(sprintf(
                "%s LIKE :%s", $key, $key
            ));
        }

        return (int) $query->executeQuery()
            ->fetchOne();
    }

    public function deleteById(string $table, string|array $ids): void
    {
        $this->validateTable($table);

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $qb = $this->db->createQueryBuilder()
            ->delete($table)
            ->where('id IN(:ids)')
            ->setParameter('ids', $ids,  ArrayParameterType::INTEGER);
        ;

        $qb->executeQuery();
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
            ->orderBy('timestamp', 'DESC')
            ->addOrderBy('index', 'DESC')
            ->setMaxResults(1)
        ;

        foreach($params as $key => $param) {
            $query->andWhere(sprintf(
                "%s LIKE :%s", $key, $key
            ));
        }

        try {
            $result = $query->executeQuery()->fetchAssociative() ?: [ ];
        } catch (\Exception) {
            $result = [ ];
        }

        return $result;
    }
}

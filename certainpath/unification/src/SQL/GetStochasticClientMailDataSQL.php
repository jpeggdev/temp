<?php

declare(strict_types=1);

namespace App\SQL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

readonly class GetStochasticClientMailDataSQL
{
    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @throws Exception
     */
    public function execute(
        int $week,
        int $year,
        int $page,
        int $perPage,
        string $sortOrder
    ): array {
        $offset = ($page - 1) * $perPage;
        $orderBy = ($sortOrder === 'ASC') ? 'ASC' : 'DESC';

        $sql = <<<SQL
            SELECT 
                b.id AS id,
                b.id AS batch_number,
                co.identifier AS "intacctId",
                co.name AS "clientName",
                c.name AS "campaign_name",
                c.id AS "campaign_id",
                c.hub_plus_product_id AS "product_id",
                bs.name AS "batch_status",
                COUNT(bp.prospect_id) AS "prospect_count",
                EXTRACT(WEEK FROM w.start_date) AS week,
                EXTRACT(ISOYEAR FROM w.start_date) AS year,
                w.start_date AS start_date,
                w.end_date AS end_date
            FROM batch b
            JOIN campaign c 
                ON c.id = b.campaign_id
            JOIN company co
                ON co.id = c.company_id
            JOIN campaign_iteration_week w
                ON w.id = b.campaign_iteration_week_id
            JOIN batch_status bs
                ON bs.id = b.batch_status_id
            LEFT JOIN batch_prospect bp
                ON bp.batch_id = b.id
            WHERE EXTRACT(WEEK FROM w.start_date) = :week
              AND EXTRACT(ISOYEAR FROM w.start_date) = :year
            GROUP BY 
                b.id,
                w.week_number,
                co.identifier,
                co.name,
                c.name,
                c.id,
                week,
                year,
                w.start_date,
                w.end_date,
                bs.name
            ORDER BY b.id $orderBy
            LIMIT :limit
            OFFSET :offset
        SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('week', $week);
        $stmt->bindValue('year', $year);
        $stmt->bindValue('limit', $perPage);
        $stmt->bindValue('offset', $offset);

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    public function countTotal(
        int $week,
        int $year
    ): int {
        $sql = <<<SQL
            SELECT COUNT(DISTINCT b.id) AS total
            FROM batch b
            JOIN campaign c 
                ON c.id = b.campaign_id
            JOIN company co
                ON co.id = c.company_id
            JOIN campaign_iteration_week w
                ON w.id = b.campaign_iteration_week_id
            JOIN batch_status bs
                ON bs.id = b.batch_status_id
            LEFT JOIN batch_prospect bp
                ON bp.batch_id = b.id
            WHERE EXTRACT(WEEK FROM w.start_date) = :week
              AND EXTRACT(ISOYEAR FROM w.start_date) = :year
        SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('week', $week);
        $stmt->bindValue('year', $year);

        $result = $stmt->executeQuery()->fetchAssociative();

        return (int) ($result['total'] ?? 0);
    }
}

<?php

declare(strict_types=1);

namespace App\SQL;

use Doctrine\DBAL\Connection;

readonly class GetTransactionListReportSQL
{
    public function __construct(private Connection $ingestConnection)
    {
    }

    public function execute(string $tenant, string $reportId): array
    {
        $sql = '
            SELECT 
                date,
                transactiontype,
                num,
                posting,
                name,
                memo,
                account,
                categories,
                amount,
                report_date,
                report_id,
                tenant
            FROM transactionlistreport
            WHERE tenant = :tenant 
              AND report_id = :report_id
              AND tenant IS NOT NULL 
              AND report_id IS NOT NULL 
              AND report_date IS NOT NULL
            ORDER BY date ASC
        ';

        $stmt = $this->ingestConnection->prepare($sql);
        $stmt->bindValue('tenant', $tenant);
        $stmt->bindValue('report_id', $reportId);

        $results = $stmt->executeQuery();

        return $results->fetchAllAssociative();
    }

    public function getAllUniqueReports(): array
    {
        $sql = '
            SELECT DISTINCT tenant, report_id
            FROM transactionlistreport
            WHERE tenant IS NOT NULL 
              AND report_id IS NOT NULL 
              AND report_date IS NOT NULL
        ';

        $stmt = $this->ingestConnection->prepare($sql);
        $results = $stmt->executeQuery();

        return $results->fetchAllAssociative();
    }
}

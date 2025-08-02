<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class LifetimeValueStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * Fetches invoices from the past 7 years and categorizes them into bins based on the number of days
     * that have passed since the invoice date. The bins are defined as follows:
     *
     * - "0-30 days"
     * - "31-90 days"
     * - "91-180 days"
     * - "181-365 days"
     * - "2 years" (366 to 730 days)
     * - "3 years" (731 to 1095 days)
     * - "4 years" (1096 to 1460 days)
     * - "5 years" (1461 to 1825 days)
     * - "6 years" (1826 to 2190 days)
     * - "7 years" (2191 to 2555 days)
     *
     * The result for each bin includes:
     *
     * [
     *     [
     *         "salesPeriod": "91-180 days",
     *         "totalSales": 42528,
     *         "salesPercentage": 0
     *     ],
     * ]
     *
     * @throws DBALException
     */
    public function createStatement(
        StochasticDashboardDTO $dto
    ): Statement {
        $sql = <<<SQL
            WITH filteredInvoices AS (
                SELECT 
                    i.total::NUMERIC AS "totalSales", 
                    CURRENT_DATE - i.invoiced_at::DATE AS daysDiff
                FROM invoice i
                JOIN company c ON i.company_id = c.id
                LEFT JOIN trade t ON i.trade_id = t.id
                LEFT JOIN prospect p ON i.customer_id = p.customer_id
                LEFT JOIN prospect_address pa ON p.id = pa.prospect_id
                LEFT JOIN address a ON pa.address_id = a.id
                WHERE c.identifier = :intacctId
                -- TRADES_CONDITION
                -- YEARS_CONDITION
                -- CITIES_CONDITION
            ),
            salesBins AS (
                SELECT
                    CASE
                        WHEN daysDiff BETWEEN 0 AND 30 THEN '0-30 days'
                        WHEN daysDiff BETWEEN 31 AND 90 THEN '31-90 days'
                        WHEN daysDiff BETWEEN 91 AND 180 THEN '91-180 days'
                        WHEN daysDiff BETWEEN 181 AND 365 THEN '181-365 days'
                        WHEN daysDiff BETWEEN 366 AND 730 THEN '2 years'
                        WHEN daysDiff BETWEEN 731 AND 1095 THEN '3 years'
                        WHEN daysDiff BETWEEN 1096 AND 1460 THEN '4 years'
                        WHEN daysDiff BETWEEN 1461 AND 1825 THEN '5 years'
                        WHEN daysDiff BETWEEN 1826 AND 2190 THEN '6 years'
                        WHEN daysDiff BETWEEN 2191 AND 2555 THEN '7 years'
                    END AS "salesPeriod",
                    SUM("totalSales") AS "totalSales"
                FROM filteredInvoices
                GROUP BY "salesPeriod"
            ),
            combinedBins AS (
                SELECT
                    expectedBins."salesPeriod",
                    COALESCE(salesBins."totalSales", 0) AS "totalSales"
                FROM (VALUES
                    ('0-30 days'),
                    ('31-90 days'),
                    ('91-180 days'),
                    ('181-365 days'),
                    ('2 years'),
                    ('3 years'),
                    ('4 years'),
                    ('5 years'),
                    ('6 years'),
                    ('7 years')
                ) AS expectedBins("salesPeriod")
                LEFT JOIN salesBins ON expectedBins."salesPeriod" = salesBins."salesPeriod"
            )
            SELECT
                "salesPeriod",
                "totalSales",
                CASE
                    WHEN (SELECT SUM("totalSales") FROM salesBins) > 0 THEN
                        ("totalSales" * 100.0) / (SELECT SUM("totalSales") FROM salesBins)
                    ELSE 0
                END AS "salesPercentage"
            FROM combinedBins
            ORDER BY
                CASE
                    WHEN "salesPeriod" = '0-30 days' THEN 1
                    WHEN "salesPeriod" = '31-90 days' THEN 2
                    WHEN "salesPeriod" = '91-180 days' THEN 3
                    WHEN "salesPeriod" = '181-365 days' THEN 4
                    WHEN "salesPeriod" = '2 years' THEN 5
                    WHEN "salesPeriod" = '3 years' THEN 6
                    WHEN "salesPeriod" = '4 years' THEN 7
                    WHEN "salesPeriod" = '5 years' THEN 8
                    WHEN "salesPeriod" = '6 years' THEN 9
                    WHEN "salesPeriod" = '7 years' THEN 10
                END;
        SQL;

        $params = $this->prepareBaseParams($dto);
        $whereConditions = $this->prepareBaseConditions($dto);
        $sql = $this->applyBaseConditions($whereConditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        return $this->bindValues($stmt, $params);
    }
}

<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class TotalSalesNewCustomerByZipCodeAndYearStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * Calculates total sales for new customers only, grouped by postal code and year.
     *
     * New customers are defined as those whose first transaction occurred in the same year
     * as the current transaction (i.e., invoice.invoiced_at).
     *
     * Format:
     * [
     *     ["postalCode" => "30319", "year" => 2024, "totalSales" => 500000],
     * ]
     *
     * @throws DBALException
     */
    public function createStatement(
        StochasticDashboardDTO $dto
    ): Statement {
        $sql = <<<SQL
            WITH first_transactions AS (
                SELECT
                    customer_id,
                    MIN(invoiced_at) AS first_transaction_date
                FROM invoice
                WHERE 
                    total > 0
                GROUP BY customer_id
            )
            SELECT 
                a.postal_code_short AS "postalCode",
                EXTRACT(YEAR FROM i.invoiced_at) AS year,
                SUM(i.total::NUMERIC) AS "totalSales"
            FROM invoice i
            JOIN first_transactions ft ON i.customer_id = ft.customer_id
            JOIN company c ON i.company_id = c.id
            LEFT JOIN trade t ON i.trade_id = t.id
            LEFT JOIN prospect p ON i.customer_id = p.customer_id
            LEFT JOIN prospect_address pa ON p.id = pa.prospect_id
            LEFT JOIN address a ON pa.address_id = a.id
            WHERE 
                c.identifier = :intacctId AND
                i.total > 0 AND
                EXTRACT(YEAR FROM i.invoiced_at) = EXTRACT(YEAR FROM ft.first_transaction_date)
                -- TRADES_CONDITION
                -- YEARS_CONDITION
                -- CITIES_CONDITION
            GROUP BY a.postal_code_short, year
            ORDER BY a.postal_code_short, year
        SQL;

        $params = $this->prepareBaseParams($dto);
        $whereConditions = $this->prepareBaseConditions($dto);
        $sql = $this->applyBaseConditions($whereConditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        return $this->bindValues($stmt, $params);
    }
}

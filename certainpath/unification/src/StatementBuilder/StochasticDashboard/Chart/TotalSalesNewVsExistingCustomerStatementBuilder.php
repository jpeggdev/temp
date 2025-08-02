<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class TotalSalesNewVsExistingCustomerStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * NC = New Customer (customers that have been with the client for less than 1 year.)
     * HF = House File (existing customers that have been with the client for more than 1 year.)
     *
     * NC or HF categorization is determined by the first transaction date (i.e., invoice.invoiced_at
     * with a non-zero-dollar amount and the time passed from that date).
     *
     * @throws DBALException
     */
    public function createStatement(StochasticDashboardDTO $dto): Statement
    {
        $sql = <<<SQL
            WITH first_transactions AS (
                SELECT
                    customer_id,
                    MIN(invoiced_at) AS first_transaction_date
                FROM invoice
                WHERE 
                    company_id = (SELECT id FROM company WHERE identifier = :intacctId)
                    AND total > 0
                GROUP BY customer_id
            )
            SELECT
                EXTRACT(YEAR FROM i.invoiced_at) AS year,
                SUM(
                    CASE
                        WHEN EXTRACT(YEAR FROM i.invoiced_at) = EXTRACT(YEAR FROM ft.first_transaction_date)
                        THEN CAST(i.total AS NUMERIC)
                        ELSE 0
                    END
                ) AS NC, -- New Customer Sales
                SUM(
                    CASE
                        WHEN EXTRACT(YEAR FROM i.invoiced_at) > EXTRACT(YEAR FROM ft.first_transaction_date)
                        THEN CAST(i.total AS NUMERIC)
                        ELSE 0
                    END
                ) AS HF -- House File Sales
            FROM invoice i
            JOIN first_transactions ft ON i.customer_id = ft.customer_id
            JOIN company c ON i.company_id = c.id
            LEFT JOIN trade t ON i.trade_id = t.id
            LEFT JOIN prospect p ON i.customer_id = p.customer_id
            LEFT JOIN prospect_address pa ON p.id = pa.prospect_id
            LEFT JOIN address a ON pa.address_id = a.id
            WHERE
                c.identifier = :intacctId AND
                i.total > 0
                -- TRADES_CONDITION
                -- YEARS_CONDITION
                -- CITIES_CONDITION
            GROUP BY year
            ORDER BY year
        SQL;

        $params = $this->prepareBaseParams($dto);
        $whereConditions = $this->prepareBaseConditions($dto);
        $sql = $this->applyBaseConditions($whereConditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);

        return $this->bindValues($stmt, $params);
    }
}

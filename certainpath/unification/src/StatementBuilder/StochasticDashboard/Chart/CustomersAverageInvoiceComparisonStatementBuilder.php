<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class CustomersAverageInvoiceComparisonStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * Computes average invoice amounts for new and repeat customers by year.
     *
     * A "new customer" is one whose first invoice is in the given year.
     * A "repeat customer" is one whose first invoice is earlier than the given year.
     *
     *  Response data structure:
     *  [
     *      [
     *          "year" => 2021,
     *          "newCustomerAvg" => 13429,
     *          "repeatCustomerAvg" => 16929,
     *      ],
     *  ]
     *
     * @throws DBALException
     */
    public function createStatement(StochasticDashboardDTO $dto): Statement
    {
        $sql = <<<SQL
            WITH first_invoices AS (
                SELECT
                    customer_id,
                    MIN(invoiced_at) AS first_invoice_date
                FROM invoice
                WHERE total > 0
                GROUP BY customer_id
            ),
            invoices_with_flags AS (
                SELECT
                    i.customer_id,
                    EXTRACT(YEAR FROM i.invoiced_at)::INT AS year,
                    i.total,
                    fi.first_invoice_date,
                    CASE
                        WHEN EXTRACT(YEAR FROM fi.first_invoice_date)::INT = EXTRACT(YEAR FROM i.invoiced_at)::INT THEN true
                        ELSE false
                    END AS is_new
                FROM invoice i
                JOIN first_invoices fi ON i.customer_id = fi.customer_id
                JOIN company c ON i.company_id = c.id
                LEFT JOIN trade t ON i.trade_id = t.id
                LEFT JOIN prospect p ON i.customer_id = p.customer_id
                LEFT JOIN prospect_address pa ON p.id = pa.prospect_id
                LEFT JOIN address a ON pa.address_id = a.id
                WHERE
                    c.identifier = :intacctId
                    AND i.total > 0
                    -- YEARS_CONDITION
                    -- TRADES_CONDITION
                    -- CITIES_CONDITION
            ),
            avg_invoices AS (
                SELECT
                    year,
                    ROUND(AVG(CASE WHEN is_new THEN total END))::INT AS "newCustomerAvg",
                    ROUND(AVG(CASE WHEN NOT is_new THEN total END))::INT AS "repeatCustomerAvg"
                FROM invoices_with_flags
                GROUP BY year
            )
            SELECT *
            FROM avg_invoices
            ORDER BY year;
        SQL;

        $params = $this->prepareBaseParams($dto);
        $conditions = $this->prepareBaseConditions($dto);
        $sql = $this->applyBaseConditions($conditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        return $this->bindValues($stmt, $params);
    }
}

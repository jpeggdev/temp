<?php

namespace App\StatementBuilder\StochasticDashboard\Table;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use App\StatementBuilder\StochasticDashboard\Chart\BaseChartDataStatementBuilder;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class PercentageOfNewCustomersChangeByZipCodeStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
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
            invoices_with_zip AS (
                SELECT
                    i.customer_id,
                    EXTRACT(YEAR FROM fi.first_invoice_date)::INT AS first_invoice_year,
                    a.postal_code_short AS "postalCode"
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
                GROUP BY i.customer_id, fi.first_invoice_date, a.postal_code_short
            ),
            first_customers_per_zip_year AS (
                SELECT
                    "postalCode",
                    first_invoice_year AS year,
                    COUNT(DISTINCT customer_id) AS new_customers
                FROM invoices_with_zip
                GROUP BY "postalCode", year
            ),
            yearly_with_lag AS (
                SELECT
                    f."postalCode",
                    f.year,
                    f.new_customers,
                    LAG(f.new_customers) OVER (PARTITION BY f."postalCode" ORDER BY f.year) AS prev_year_customers
                FROM first_customers_per_zip_year f
            ),
            final_table AS (
                SELECT
                    "postalCode",
                    year,
                    new_customers AS nc_count,
                    CASE
                        WHEN prev_year_customers IS NULL THEN NULL
                        WHEN prev_year_customers = 0 THEN NULL
                        ELSE ROUND((new_customers - prev_year_customers) * 100.0 / prev_year_customers, 2)
                    END AS percent_change
                FROM yearly_with_lag
            )
            SELECT
                "postalCode",
                year,
                nc_count,
                percent_change
            FROM final_table
            ORDER BY "postalCode", year;
        SQL;


        $params = $this->prepareStatementParams($dto);
        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);

        return $this->bindValues($stmt, $params);
    }

    private function prepareStatementParams(StochasticDashboardDTO $dto): array
    {
        return [
            'intacctId' => $dto->intacctId,
        ];
    }
}

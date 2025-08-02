<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class PercentageOfNewCustomersByZipCodeStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * Computes the percentage of new customers by ZIP code for each year.
     *
     * A "new customer" is defined as one whose first invoice date falls within a given year.
     * The percentage is calculated for each zip/year pair as:
     *   (# new customers in zip/year) / (# total customers in zip/year) * 100
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
            invoices_with_zip AS (
                SELECT
                    i.customer_id,
                    EXTRACT(YEAR FROM i.invoiced_at)::INT AS year,
                    a.postal_code_short AS "postalCode",
                    fi.first_invoice_date
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
                GROUP BY i.customer_id, year, a.postal_code_short, fi.first_invoice_date
            ),
            all_customers_per_zip_year AS (
                SELECT
                    "postalCode",
                    year,
                    COUNT(DISTINCT customer_id) AS total_customers
                FROM invoices_with_zip
                GROUP BY "postalCode", year
            ),
            new_customers_per_zip_year AS (
                SELECT
                    "postalCode",
                    year,
                    COUNT(DISTINCT customer_id) AS new_customers
                FROM invoices_with_zip
                WHERE DATE_TRUNC('year', first_invoice_date) = DATE_TRUNC('year', (year || '-01-01')::date)
                GROUP BY "postalCode", year
            )
            SELECT
                ac."postalCode",
                ac.year,
                ROUND(
                    COALESCE(nc.new_customers, 0) * 100.0 / NULLIF(ac.total_customers, 0),
                    0
                )::INT AS percentage
            FROM all_customers_per_zip_year ac
            LEFT JOIN new_customers_per_zip_year nc
                ON ac."postalCode" = nc."postalCode" AND ac.year = nc.year
            ORDER BY ac."postalCode", ac.year
        SQL;

        $params = $this->prepareBaseParams($dto);
        $conditions = $this->prepareBaseConditions($dto);
        $sql = $this->applyBaseConditions($conditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        return $this->bindValues($stmt, $params);
    }
}

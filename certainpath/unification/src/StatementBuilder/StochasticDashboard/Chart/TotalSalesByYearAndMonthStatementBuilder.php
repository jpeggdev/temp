<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class TotalSalesByYearAndMonthStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * @throws DBALException
     */
    public function createStatement(StochasticDashboardDTO $dto): Statement
    {
        $sql = <<<SQL
            SELECT
                y.year,
                m.month,
                COALESCE(SUM(CAST(i.total AS NUMERIC)), 0) AS total_sales
            FROM
                (
                    SELECT DISTINCT EXTRACT(YEAR FROM i.invoiced_at)::int AS year
                    FROM invoice i
                    JOIN company c ON i.company_id = c.id
                    WHERE i.total > 0 AND c.identifier = :intacctId::text
                    -- YEARS_YEARS_CONDITION
                ) y
            CROSS JOIN generate_series(1, 12) AS m(month)
            LEFT JOIN invoice i
                ON EXTRACT(YEAR FROM i.invoiced_at)::int = y.year
                AND EXTRACT(MONTH FROM i.invoiced_at) = m.month
                AND i.total > 0
                AND i.company_id = (
                    SELECT id FROM company WHERE identifier = :intacctId::text
                )
            LEFT JOIN trade t ON i.trade_id = t.id
            LEFT JOIN prospect p ON i.customer_id = p.customer_id
            LEFT JOIN prospect_address pa ON p.id = pa.prospect_id
            LEFT JOIN address a ON pa.address_id = a.id
            WHERE TRUE
            -- TRADES_CONDITION
            -- CITIES_CONDITION
            GROUP BY y.year, m.month
            ORDER BY y.year, m.month;
        SQL;

        $params = $this->prepareStatementParams($dto);
        $statementConditions = $this->prepareStatementConditions($dto);
        $sql = $this->applyStatementConditions($statementConditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);

        return $this->bindValues($stmt, $params);
    }

    private function prepareStatementConditions($dto): array
    {
        $conditions = $this->prepareBaseConditions($dto);

        if (!empty($dto->years)) {
            $conditions['yearsYears'] = $this->getYearsYearsCondition();
        }

        return $conditions;
    }

    private function applyStatementConditions($statementConditions, $params, $sql): string
    {
        $sql = $this->applyBaseConditions($statementConditions, $params, $sql);

        return str_replace(
            ['-- YEARS_YEARS_CONDITION'],
            [
                !empty($statementConditions['yearsYears']) && isset($params['yearsYears'])
                    ? 'AND ' . $statementConditions['yearsYears']
                    : '',
            ],
            $sql
        );
    }

    private function prepareStatementParams(StochasticDashboardDTO $dto): array
    {
        $params = $this->prepareBaseParams($dto);

        if (!empty($dto->years)) {
            $params['yearsYears'] = $dto->years;
        }

        return $params;
    }

    private function getYearsYearsCondition(): string
    {
        return 'EXTRACT(YEAR FROM i.invoiced_at)::int = ANY(:yearsYears::INT[])';
    }
}

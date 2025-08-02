<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class TotalSalesByZipCodeStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * Calculates total sales grouped by postal code (postal_code_short) with applied filters.
     *
     * The formatted data has the following format:
     * [
     *     [
     *         "postalCode": "30319",
     *         "total": 3,427,506
     *     ],
     * ]
     *
     * @throws DBALException
     */
    public function createStatement(
        StochasticDashboardDTO $dto
    ): Statement {
        $sql = <<<SQL
            SELECT 
                a.postal_code_short AS "postalCode",
                SUM(i.total::NUMERIC) AS "totalSales"
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
            GROUP BY a.postal_code_short
            ORDER BY "totalSales" DESC
        SQL;

        $params = $this->prepareBaseParams($dto);
        $whereConditions = $this->prepareBaseConditions($dto);
        $sql = $this->applyBaseConditions($whereConditions, $params, $sql);

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        return $this->bindValues($stmt, $params);
    }
}

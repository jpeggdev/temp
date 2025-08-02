<?php

namespace App\StatementBuilder\StochasticDashboard\Chart;

use App\DTO\Query\Chart\StochasticDashboardDTO;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;

readonly class LifetimeValueByTierStatementBuilder extends BaseChartDataStatementBuilder
{
    /**
     * @throws DBALException
     */
    public function createStatement(
        StochasticDashboardDTO $dto
    ): Statement {
        $sql = <<<SQL
            WITH filteredInvoices AS (
                SELECT 
                    p.preferred_address_id AS household_id,
                    i.total::NUMERIC AS invoiceTotal,
                    i.invoiced_at,
                    c.identifier,
                    t.name AS trade,
                    a.city
                FROM invoice i
                JOIN company c ON i.company_id = c.id
                LEFT JOIN trade t ON i.trade_id = t.id
                LEFT JOIN prospect p ON i.customer_id = p.customer_id
                JOIN address a ON p.preferred_address_id = a.id
                WHERE c.identifier = :intacctId
                -- TRADES_CONDITION
                -- YEARS_CONDITION
                -- CITIES_CONDITION
            ),
            householdLifetimeValue AS (
                SELECT 
                    household_id,
                    SUM(invoiceTotal) AS totalLTV
                FROM filteredInvoices
                GROUP BY household_id
            ),
            tieredHouseholds AS (
                SELECT 
                    household_id,
                    totalLTV,
                    CASE
                        WHEN totalLTV <= 500 THEN 'Up to \$500'
                        WHEN totalLTV <= 1000 THEN 'Greater than \$500'
                        WHEN totalLTV <= 2500 THEN 'Greater than \$1,000'
                        WHEN totalLTV <= 5000 THEN 'Greater than \$2,500'
                        WHEN totalLTV <= 10000 THEN 'Greater than \$5,000'
                        WHEN totalLTV <= 20000 THEN 'Greater than \$10,000'
                        WHEN totalLTV <= 30000 THEN 'Greater than \$20,000'
                        ELSE 'Greater than \$30,000'
                    END AS tier
                FROM householdLifetimeValue
            ),
            tierCounts AS (
                SELECT 
                    tier,
                    COUNT(DISTINCT household_id) AS "householdCount"
                FROM tieredHouseholds
                GROUP BY tier
            ),
            tierTotals AS (
                SELECT 
                    tier,
                    SUM(totalLTV)::BIGINT AS "totalSales"
                FROM tieredHouseholds
                GROUP BY tier
            ),
            expectedTiers AS (
                SELECT * FROM (VALUES
                    ('Up to \$500'),
                    ('Greater than \$500'),
                    ('Greater than \$1,000'),
                    ('Greater than \$2,500'),
                    ('Greater than \$5,000'),
                    ('Greater than \$10,000'),
                    ('Greater than \$20,000'),
                    ('Greater than \$30,000')
                ) AS tiers(tier)
            )
            SELECT 
                expectedTiers.tier,
                COALESCE(tierCounts."householdCount", 0) AS "householdCount",
                COALESCE(tierTotals."totalSales", 0) AS "totalSales"
            FROM expectedTiers
            LEFT JOIN tierCounts ON expectedTiers.tier = tierCounts.tier
            LEFT JOIN tierTotals ON expectedTiers.tier = tierTotals.tier
            ORDER BY 
                CASE 
                    WHEN expectedTiers.tier = 'Up to \$500' THEN 1
                    WHEN expectedTiers.tier = 'Greater than \$500' THEN 2
                    WHEN expectedTiers.tier = 'Greater than \$1,000' THEN 3
                    WHEN expectedTiers.tier = 'Greater than \$2,500' THEN 4
                    WHEN expectedTiers.tier = 'Greater than \$5,000' THEN 5
                    WHEN expectedTiers.tier = 'Greater than \$10,000' THEN 6
                    WHEN expectedTiers.tier = 'Greater than \$20,000' THEN 7
                    WHEN expectedTiers.tier = 'Greater than \$30,000' THEN 8
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

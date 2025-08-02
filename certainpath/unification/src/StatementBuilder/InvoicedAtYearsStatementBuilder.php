<?php

namespace App\StatementBuilder;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Statement;

readonly class InvoicedAtYearsStatementBuilder extends AbstractStatementBuilder
{
    /**
     * @throws Exception
     */
    public function createStatement(
        string $intacctId = null,
        ?int $limit = null,
        ?int $offset = null,
        string $sortBy = 'year',
        string $sortOrder = 'DESC',
        ?string $searchTerm = null
    ): Statement {
        $sortOrder = $this->sanitizeSortOrder($sortOrder);

        $sql = <<<SQL
            SELECT DISTINCT CAST(EXTRACT(YEAR FROM i.invoiced_at) AS INT) AS year 
            FROM invoice i
            JOIN company c ON i.company_id = c.id
            WHERE c.identifier = :intacctId
            --- SEARCH_TERM_CONDITION
            ORDER BY $sortBy $sortOrder
            --- LIMIT_CONDITION
            --- OFFSET_CONDITION
        SQL;

        $sql = $this->applySearchTermCondition($sql, $searchTerm);
        $sql = $this->applyLimitCondition($sql, $limit);
        $sql = $this->applyOffsetCondition($sql, $offset);

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('intacctId', $intacctId);

        if ($searchTerm !== null) {
            $stmt->bindValue('searchTerm', "%$searchTerm%", \PDO::PARAM_STR);
        }
        if ($limit !== null) {
            $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        }
        if ($offset !== null) {
            $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        }

        return $stmt;
    }

    private function applySearchTermCondition(string $sql, ?string $searchTerm = null): string
    {
        $searchTermCondition = $searchTerm !== null
            ? 'AND CAST(EXTRACT(YEAR FROM i.invoiced_at) AS INT)::TEXT LIKE :searchTerm'
            : '';

        return str_replace('--- SEARCH_TERM_CONDITION', $searchTermCondition, $sql);
    }

    private function applyLimitCondition(string $sql, ?int $limit = null): string
    {
        $limitCondition = $limit !== null
            ? 'LIMIT :limit'
            : '';

        return str_replace('--- LIMIT_CONDITION', $limitCondition, $sql);
    }

    private function applyOffsetCondition(string $sql, ?int $offset = null): string
    {
        $offsetCondition = $offset !== null
            ? 'OFFSET :offset'
            : '';

        return str_replace('--- OFFSET_CONDITION', $offsetCondition, $sql);
    }
}

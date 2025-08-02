<?php

namespace App\StatementBuilder;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Statement;
use Doctrine\ORM\EntityManagerInterface;

readonly abstract class AbstractStatementBuilder
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {
    }

    /**
     * @throws DBALException
     */
    protected function bindValues(Statement $stmt, array $params): Statement
    {
        foreach ($params as $key => $param) {
            if (is_array($param)) {
                $stmt->bindValue($key, '{' . implode(',', $param) . '}', \PDO::PARAM_STR);
            } else {
                $stmt->bindValue($key, $param, \PDO::PARAM_STR);
            }
        }

        return $stmt;
    }

    protected function sanitizeSortOrder($sortOrder): string
    {
        $sortOrder = strtoupper($sortOrder);
        $allowedSortOrders = ['ASC', 'DESC'];

        return in_array($sortOrder, $allowedSortOrders, true)
            ? $sortOrder
            : $allowedSortOrders[0];
    }
}

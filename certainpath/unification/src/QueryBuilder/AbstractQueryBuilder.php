<?php

namespace App\QueryBuilder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

readonly abstract class AbstractQueryBuilder
{
    public function __construct(
        protected EntityManagerInterface $em
    ) {
    }

    abstract protected function createBaseQueryBuilder(): QueryBuilder;

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    protected function sanitizeSortOrder($sortOrder): string
    {
        $sortOrder = strtoupper($sortOrder);
        $allowedSortOrders = ['ASC', 'DESC'];

        return in_array($sortOrder, $allowedSortOrders, true)
            ? $sortOrder
            : $allowedSortOrders[0];
    }

    protected function aliasExists(QueryBuilder $queryBuilder, string $alias): bool
    {
        return in_array($alias, $queryBuilder->getAllAliases(), true);
    }

    protected function setLimit(?int $limit, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder;
    }

    protected function setOffset(?int $offset, QueryBuilder $queryBuilder): QueryBuilder
    {
        if ($offset) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder;
    }
}
